<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Process;
use Livewire\Component;

class SentimentAnalyzer extends Component
{
    public string $text = '';

    public ?array $result = null;

    public ?string $error = null;

    public bool $loading = false;

    public function updatedText(): void
    {
        // Clear previous results when text changes
        if ($this->result || $this->error) {
            $this->reset(['result', 'error']);
        }
    }

    public function analyze(): void
    {
        $this->loading = true;
        $this->error = null;
        $this->result = null;

        // Validate input
        $this->validate([
            'text' => 'required|min:3|max:2000',
        ], [
            'text.required' => 'Please enter some text to analyze.',
            'text.min' => 'Text must be at least 3 characters long.',
            'text.max' => 'Text cannot exceed 2000 characters.',
        ]);

        try {
            // Path to your Python script
            $scriptPath = base_path('scripts/sentiment_analyzer.py');

            // Run the Python script using Laravel 12 Process facade
            // Pass text via stdin for better cross-platform compatibility
            $process = Process::timeout(30)
                ->input($this->text)
                ->run(['python3', $scriptPath]);

            // Check if process was successful
            if ($process->successful()) {
                $output = trim($process->output());
                $decoded = json_decode($output, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error = 'Failed to parse Python script output: '.json_last_error_msg();
                } else {
                    $this->result = $decoded;
                }
            } else {
                $errorOutput = $process->errorOutput();
                $this->error = 'Analysis failed: '.($errorOutput ?: 'Unknown error occurred');
            }

        } catch (\Exception $e) {
            $this->error = 'Error: '.$e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function clear(): void
    {
        $this->reset(['text', 'result', 'error']);
    }

    public function getCharacterCountProperty(): int
    {
        return mb_strlen($this->text);
    }

    public function getWordCountProperty(): int
    {
        if (empty(trim($this->text))) {
            return 0;
        }

        return count(preg_split('/\s+/', trim($this->text)));
    }

    public function render()
    {
        return view('livewire.sentiment-analyzer')->layout('components.layouts.app', [
            'title' => 'Sentiment Analyzer',
        ]);
    }
}
