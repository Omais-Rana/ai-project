@extends('layouts.app')

@section('title', 'Chat with ' . $document->original_name)

@section('content')
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('documents.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Documents
            </a>
        </div>

        <!-- Document Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $document->original_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $document->getFileSizeFormatted() }} • {{ $document->total_chunks }} chunks processed
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        ✓ Ready
                    </span>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div 
            x-data="chatInterface()" 
            x-init="init()"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
            style="height: calc(100vh - 300px); min-height: 500px;"
        >
            <!-- Messages Container -->
            <div 
                x-ref="messagesContainer"
                class="h-full overflow-y-auto p-6 space-y-4"
                style="height: calc(100% - 80px);"
            >
                <!-- Welcome Message -->
                <div x-show="messages.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Ask me anything!</h3>
                    <p class="mt-1 text-sm text-gray-500">I'll answer based on the content of this document.</p>
                </div>

                <!-- Messages -->
                <template x-for="(message, index) in messages" :key="index">
                    <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="message.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900'" 
                             class="max-w-3xl rounded-lg px-4 py-3">
                            <div class="text-sm font-medium mb-1" x-text="message.role === 'user' ? 'You' : 'Assistant'"></div>
                            <div class="text-sm" x-html="formatMarkdown(message.content)"></div>
                            
                            <!-- Citations -->
                            <template x-if="message.citations && message.citations.length > 0">
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="text-xs font-medium mb-2">Sources:</div>
                                    <template x-for="citation in message.citations" :key="citation.chunk_id">
                                        <div class="text-xs opacity-75">
                                            📄 <span x-text="citation.document_name"></span>
                                            <span x-show="citation.page"> (Page <span x-text="citation.page"></span>)</span>
                                            - Similarity: <span x-text="(citation.similarity * 100).toFixed(0) + '%'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Confidence Badge -->
                            <template x-if="message.confidence_percentage">
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                          :class="message.is_high_confidence ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                        <span x-text="message.confidence_percentage + '% confidence'"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
                <div x-show="loading" class="flex justify-start">
                    <div class="bg-gray-100 rounded-lg px-4 py-3">
                        <div class="flex items-center space-x-2">
                            <div class="animate-bounce">💭</div>
                            <span class="text-sm text-gray-600">Thinking...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 p-4">
                <form @submit.prevent="sendMessage" class="flex space-x-2">
                    <input 
                        type="text" 
                        x-model="question"
                        :disabled="loading"
                        placeholder="Ask a question about this document..."
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100"
                    />
                    <button 
                        type="submit"
                        :disabled="loading || !question.trim()"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">Send</span>
                        <span x-show="loading">...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function chatInterface() {
            return {
                messages: [],
                question: '',
                loading: false,
                conversationId: null,

                init() {
                    this.conversationId = this.generateUUID();
                    this.loadChatHistory();
                },

                async loadChatHistory() {
                    // Try to load from database first
                    try {
                        const response = await fetch('{{ route('documents.document-history', $document) }}', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            
                            if (data.success && data.data.length > 0) {
                                // Convert database records to chat messages
                                this.messages = [];
                                data.data.forEach(record => {
                                    // Add user question
                                    this.messages.push({
                                        role: 'user',
                                        content: record.question
                                    });
                                    // Add assistant answer
                                    this.messages.push({
                                        role: 'assistant',
                                        content: record.answer,
                                        citations: record.citations,
                                        confidence_percentage: record.confidence_percentage || Math.round(record.confidence * 100),
                                        is_high_confidence: record.confidence > 0.7
                                    });
                                });
                                
                                // Save to localStorage as backup
                                localStorage.setItem('chat_{{ $document->id }}', JSON.stringify(this.messages));
                                this.scrollToBottom();
                                return;
                            }
                        }
                    } catch (error) {
                        console.log('Database history failed, trying localStorage');
                    }

                    // Fallback to localStorage
                    try {
                        const saved = localStorage.getItem('chat_{{ $document->id }}');
                        if (saved) {
                            this.messages = JSON.parse(saved);
                            this.scrollToBottom();
                        }
                    } catch (error) {
                        console.log('Starting with empty chat');
                    }
                },

                saveChatToStorage() {
                    try {
                        localStorage.setItem('chat_{{ $document->id }}', JSON.stringify(this.messages));
                    } catch (error) {
                        // localStorage full or disabled
                    }
                },

                async sendMessage() {
                    if (!this.question.trim() || this.loading) return;

                    const userMessage = this.question;
                    this.messages.push({
                        role: 'user',
                        content: userMessage
                    });

                    this.question = '';
                    this.loading = true;
                    this.scrollToBottom();

                    try {
                        const response = await fetch('{{ route('documents.ask', $document) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                question: userMessage,
                                conversation_id: this.conversationId
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.messages.push({
                                role: 'assistant',
                                content: data.data.answer,
                                citations: data.data.citations,
                                confidence_percentage: data.data.confidence_percentage,
                                is_high_confidence: data.data.is_high_confidence
                            });
                            
                            // Save to localStorage
                            this.saveChatToStorage();
                        } else {
                            this.messages.push({
                                role: 'assistant',
                                content: 'Sorry, I encountered an error: ' + data.message
                            });
                        }
                    } catch (error) {
                        this.messages.push({
                            role: 'assistant',
                            content: 'Sorry, I encountered an error. Please try again.'
                        });
                        console.error('Error:', error);
                    } finally {
                        this.loading = false;
                        this.saveChatToStorage();
                        this.scrollToBottom();
                    }
                },

                formatMarkdown(text) {
                    // Simple markdown formatter for **bold** text
                    return text
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.messagesContainer;
                        container.scrollTop = container.scrollHeight;
                    });
                },

                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
        }
    </script>
    @endpush
@endsection
