<div class="mx-auto container space-y-6 p-6">
    <div class="space-y-4">
        <flux:heading size="xl" level="1">Sentiment Analyzer</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">
            Analyze the emotional tone of your text. Enter text below and get instant sentiment analysis with detailed metrics.
        </flux:text>
    </div>

    <form wire:submit="analyze" class="space-y-6">
        <flux:field>
            <flux:textarea
                wire:model.live="text"
                label="Enter text to analyze"
                placeholder="Type something like: This is a great and wonderful day! I'm so happy and excited about this amazing opportunity."
                rows="6"
                :invalid="$errors->has('text')"
                :disabled="$loading"
                description="Enter at least 3 characters. Maximum 2000 characters allowed."
            />
            <flux:error name="text" />
            
            @if($text)
                <div class="mt-2 flex gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                    <span>Characters: <strong>{{ $this->characterCount }}</strong></span>
                    <span>Words: <strong>{{ $this->wordCount }}</strong></span>
                </div>
            @endif
        </flux:field>

        <div class="flex items-center gap-3">
            <flux:button
                type="submit"
                variant="primary"
                icon="sparkles"
                wire:loading.attr="disabled"
                :disabled="$loading"
            >
                <span wire:loading.remove wire:target="analyze">Analyze Sentiment</span>
                <span wire:loading wire:target="analyze">Analyzing...</span>
            </flux:button>

            @if($result || $error || $text)
                <flux:button
                    type="button"
                    variant="ghost"
                    icon="x-mark"
                    wire:click="clear"
                    wire:loading.attr="disabled"
                >
                    Clear
                </flux:button>
            @endif
        </div>
    </form>

    @if($error)
        <flux:callout
            variant="danger"
            icon="exclamation-circle"
            heading="Analysis Error"
        >
            {{ $error }}
        </flux:callout>
    @endif

    @if($result)
        <div class="space-y-6">
            <!-- Main Sentiment Result -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg" level="2">Analysis Results</flux:heading>
                    <flux:badge
                        color="{{ $result['sentiment'] === 'positive' ? 'green' : ($result['sentiment'] === 'negative' ? 'red' : 'gray') }}"
                        size="lg"
                        icon="{{ $result['sentiment'] === 'positive' ? 'check-circle' : ($result['sentiment'] === 'negative' ? 'x-circle' : 'minus') }}"
                    >
                        {{ ucfirst($result['sentiment']) }}
                    </flux:badge>
                </div>

                <!-- Sentiment Percentage Bar -->
                @if(isset($result['sentiment_percentage']))
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">Sentiment Score</span>
                            <span class="font-semibold">{{ $result['sentiment_percentage'] }}%</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div
                                class="h-full transition-all duration-500 {{ $result['sentiment'] === 'positive' ? 'bg-green-500' : ($result['sentiment'] === 'negative' ? 'bg-red-500' : 'bg-zinc-500') }}"
                                style="width: {{ $result['sentiment_percentage'] }}%"
                            ></div>
                        </div>
                    </div>
                @endif

                <!-- Metrics Grid -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Confidence
                        </div>
                        <div class="mt-2 text-2xl font-bold">
                            {{ $result['confidence'] ?? 'N/A' }}%
                        </div>
                        <div class="mt-1 text-xs text-zinc-500">
                            Analysis confidence level
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            Word Count
                        </div>
                        <div class="mt-2 text-2xl font-bold">
                            {{ $result['word_count'] ?? 0 }}
                        </div>
                        <div class="mt-1 text-xs text-zinc-500">
                            Total words in text
                        </div>
                    </div>

                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <div class="text-sm font-medium text-green-700 dark:text-green-400">
                            Positive Words
                        </div>
                        <div class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $result['positive_words_found'] ?? 0 }}
                        </div>
                        <div class="mt-1 text-xs text-green-600/70 dark:text-green-400/70">
                            Detected positive sentiment words
                        </div>
                    </div>

                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="text-sm font-medium text-red-700 dark:text-red-400">
                            Negative Words
                        </div>
                        <div class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ $result['negative_words_found'] ?? 0 }}
                        </div>
                        <div class="mt-1 text-xs text-red-600/70 dark:text-red-400/70">
                            Detected negative sentiment words
                        </div>
                    </div>
                </div>

                <!-- Additional Metrics -->
                @if(isset($result['sentiment_words_total']) || isset($result['score']))
                    <div class="grid gap-4 md:grid-cols-2">
                        @if(isset($result['score']))
                            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Sentiment Score
                                </div>
                                <div class="mt-2 text-2xl font-bold">
                                    {{ $result['score'] }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    Difference between positive and negative words
                                </div>
                            </div>
                        @endif

                        @if(isset($result['sentiment_words_total']))
                            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    Sentiment Words
                                </div>
                                <div class="mt-2 text-2xl font-bold">
                                    {{ $result['sentiment_words_total'] }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    Total detected sentiment words
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>