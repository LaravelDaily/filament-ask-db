<?php

namespace App\Filament\Pages;

use App\Services\GPTEngine;
use Exception;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class Chat extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.chat';
    public ?array $data = [];
    public bool $waitingForResponse = false;
    public string $reply = '';
    public string $lastQuestion = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->required()
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $this->reply = '';
        $this->waitingForResponse = true;
        $message = $this->form->getState()['message'];
        $this->data['message'] = '';

        $this->dispatch('queryAI', $message);
    }

    #[On('queryAI')]
    public function queryAI($message)
    {
        try {
            $this->reply = (new GPTEngine())->ask($message);
        } catch (Exception $e) {
            info($e->getMessage());
            $this->reply = 'Sorry, the AI assistant was unable to answer your question. Please try to rephrase your question.';
            $this->data['message'] = $this->lastQuestion;
        }
        $this->lastQuestion = $message;
        $this->waitingForResponse = false;
    }
}
