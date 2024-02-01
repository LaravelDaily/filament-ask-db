<?php

namespace App\Services;

use App\Exceptions\UnsafeQueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenAI;
use stdClass;

/**
 * Code base taken from abandoned GitHub repo:
 * https://github.com/beyondcode/laravel-ask-database/tree/main
 */
class GPTEngine
{
    protected string $connection;
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.key'));
        $this->connection = config('ask-database.connection');
    }

    public function ask(string $question): string
    {
        $query = $this->getQuery($question);

        $result = json_encode($this->evaluateQuery($query));

        $prompt = $this->buildPrompt($question, $query, $result);

        $answer = $this->queryOpenAi($prompt, "\n", 0.7);

        return Str::of($answer)
            ->trim()
            ->trim('"');
    }

    public function getQuery(string $question): string
    {
        $prompt = $this->buildPrompt($question);

        $query = $this->queryOpenAi($prompt, "\n");
        $query = Str::of($query)
            ->trim()
            ->trim('"');

        $this->ensureQueryIsSafe($query);

        info($query);

        return $query;
    }

    protected function queryOpenAi(string $prompt, string $stop, float $temperature = 0.0)
    {
        $completions = $this->client->chat()->create([
            'model' => 'gpt-4-1106-preview',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt,
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => 100,
            'stop' => $stop,
        ]);

        return $completions->choices[0]->message->content;
    }

    protected function buildPrompt(string $question, string $query = null, string $result = null): string
    {
        $tables = $this->getTables($question);

        $prompt = (string)view('ask-database.query', [
            'question' => $question,
            'tables' => $tables,
            'dialect' => $this->getDialect(),
            'query' => $query,
            'result' => $result,
        ]);

        return rtrim($prompt, PHP_EOL);
    }

    protected function evaluateQuery(string $query): object
    {
        return DB::connection($this->connection)->select($this->getRawQuery($query))[0] ?? new stdClass();
    }

    protected function getRawQuery(string $query): string
    {
        if (version_compare(app()->version(), '10.0', '<')) {
            /* @phpstan-ignore-next-line */
            return (string)DB::raw($query);
        }

        return DB::raw($query)->getValue(DB::connection($this->connection)->getQueryGrammar());
    }

    /**
     * @throws UnsafeQueryException
     */
    protected function ensureQueryIsSafe(string $query): void
    {
        $query = strtolower($query);
        // Update, Delete, Create - all of them have extra spaces to avoid matching with `created_at` or `updated_at` or `deleted_at` columns
        $forbiddenWords = ['insert', 'update ', 'delete ', 'alter', 'drop', 'truncate', 'create ', 'replace'];

        throw_if(Str::contains($query, $forbiddenWords), UnsafeQueryException::fromQuery($query));
    }

    protected function getDialect(): string
    {
        $databasePlatform = DB::connection($this->connection)->getDoctrineConnection()->getDatabasePlatform();

        return Str::before(class_basename($databasePlatform), 'Platform');
    }

    protected function getTables(string $question): array
    {
        return once(function () use ($question) {
            $tables = DB::connection($this->connection)
                ->getDoctrineSchemaManager()
                ->listTables();

            if (count($tables) < config('ask-database.max_tables_before_performing_lookup')) {
                return $tables;
            }

            return $this->filterMatchingTables($question, $tables);
        });
    }

    protected function filterMatchingTables(string $question, array $tables): array
    {
        $prompt = (string)view('ask-database.tables', [
            'question' => $question,
            'tables' => $tables,
        ]);
        $prompt = rtrim($prompt, PHP_EOL);

        $matchingTablesResult = $this->queryOpenAi($prompt, "\n");

        $matchingTables = Str::of($matchingTablesResult)
            ->explode(',')
            ->transform(fn(string $tableName) => strtolower(trim($tableName)));

        return collect($tables)->filter(function ($table) use ($matchingTables) {
            return $matchingTables->contains(strtolower($table->getName()));
        })->toArray();
    }
}