# Document Q&A Platform - Technical Architecture

## Overview

This platform is a **Retrieval-Augmented Generation (RAG)** system built on **Laravel 13** that allows users to upload documents and ask questions about their content. The system combines **document parsing**, **vector embeddings**, **semantic search**, and **AI-powered question answering** using **Mistral AI**.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                           USER INTERFACE                           │
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐   │
│  │   Upload Form   │  │   Document List  │  │   Chat Interface│   │
│  │   (Blade/JS)    │  │    (Blade/CSS)   │  │  (Alpine.js)   │   │
│  └─────────────────┘  └──────────────────┘  └─────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        LARAVEL BACKEND                             │
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐   │
│  │DocumentController│  │DocumentQuestion │  │   API Routes    │   │
│  │                 │  │   Controller     │  │                 │   │
│  └─────────────────┘  └──────────────────┘  └─────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        SERVICE LAYER                               │
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐   │
│  │Document         │  │Vector Search     │  │Document Question│   │
│  │Processor        │  │Service           │  │Service          │   │
│  │Service          │  │                  │  │                 │   │
│  └─────────────────┘  └──────────────────┘  └─────────────────┘   │
│  ┌─────────────────┐  ┌──────────────────┐                       │
│  │Embedding        │  │Document Parsers  │                       │
│  │Service          │  │(PDF/DOCX/TXT)    │                       │
│  │                 │  │                  │                       │
│  └─────────────────┘  └──────────────────┘                       │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         AI LAYER                                   │
│  ┌─────────────────┐  ┌──────────────────┐                       │
│  │Mistral AI API   │  │Document Question │                       │
│  │(Embeddings)     │  │Agent             │                       │
│  │                 │  │                  │                       │
│  └─────────────────┘  └──────────────────┘                       │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                                  │
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐   │
│  │   Documents     │  │ Document Chunks  │  │Document Questions│   │
│  │   MySQL Table   │  │   MySQL Table    │  │   MySQL Table   │   │
│  └─────────────────┘  └──────────────────┘  └─────────────────┘   │
│  ┌─────────────────┐  ┌──────────────────┐                       │
│  │  File Storage   │  │   Cache Layer    │                       │
│  │ (Laravel/Disk)  │  │   (Embeddings)   │                       │
│  └─────────────────┘  └──────────────────┘                       │
└─────────────────────────────────────────────────────────────────────┘
```

## Technology Stack

### Backend Framework

- **Laravel 13** - Modern PHP framework with built-in AI SDK support
- **PHP 8.3+** - Latest PHP features and performance improvements

### AI & Machine Learning

- **Laravel AI SDK 0.4.2** - First-party Laravel AI integration
- **Mistral AI API** - Large language model for text generation and embeddings
- **Mistral-Large-Latest** - Primary model for question answering
- **Mistral-Embed** - Embedding model for vector representations

### Frontend Stack

- **Blade Templates** - Laravel's templating engine
- **Alpine.js** - Lightweight JavaScript framework for interactivity
- **Tailwind CSS** - Utility-first CSS framework for styling
- **Vite** - Modern build tool for asset compilation

### Storage & Database

- **MySQL** - Primary database for structured data
- **Laravel File Storage** - Document file management
- **Laravel Cache** - Embedding caching for performance

## Document Processing Pipeline

### 1. File Upload & Validation

```php
// DocumentController@store
$request->validate([
    'file' => 'required|file|mimes:pdf,docx,doc,txt|max:10240'
]);
```

**Supported Formats:**

- **TXT** - Plain text files (native support)
- **PDF** - Portable Document Format (requires `smalot/pdfparser`)
- **DOCX** - Microsoft Word documents (requires `phpoffice/phpword`)

### 2. Document Storage

```php
// Store file with UUID naming
$filename = Str::uuid() . '.' . $extension;
$path = $file->storeAs('documents', $filename, 'local');

// Create database record
Document::create([
    'original_name' => $originalName,
    'file_path' => $path,
    'file_size' => $file->getSize(),
    'file_type' => $extension,
    'user_id' => null, // No authentication required
    'status' => 'uploaded'
]);
```

### 3. Document Parsing

The `DocumentProcessorService` orchestrates the parsing process:

```php
// Get appropriate parser based on file type
$parser = $this->getParser($document->file_type);
$chunks = $parser->parseWithMetadata($filePath);
```

**Parser Implementation:**

- **TxtParser** - Reads plain text files directly
- **PdfParser** - Extracts text using `smalot/pdfparser` library
- **DocxParser** - Extracts text using `phpoffice/phpword` library

Each parser returns structured data:

```php
[
    [
        'text' => 'Page content...',
        'metadata' => [
            'page' => 1,
            'section' => 'Introduction'
        ]
    ]
]
```

### 4. Text Chunking Strategy

**Smart Chunking Algorithm:**

- **Chunk Size**: 800 characters (configurable)
- **Overlap**: 150 characters (prevents information loss at boundaries)
- **Method**: Sentence-aware splitting to avoid breaking mid-sentence

```php
public function chunkText(string $text): array
{
    // Split by sentences first
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    $chunks = [];
    $currentChunk = '';

    foreach ($sentences as $sentence) {
        $testChunk = $currentChunk . ' ' . $sentence;

        if (strlen($testChunk) <= $this->chunkSize) {
            $currentChunk = $testChunk;
        } else {
            // Save current chunk
            if (!empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
            }

            // Start new chunk with overlap
            $words = explode(' ', $currentChunk);
            $overlapWords = array_slice($words, -($this->chunkOverlap / 10));
            $currentChunk = implode(' ', $overlapWords) . ' ' . $sentence;
        }
    }

    // Add final chunk
    if (!empty($currentChunk)) {
        $chunks[] = trim($currentChunk);
    }

    return $chunks;
}
```

### 5. Chunk Storage

Each chunk is stored with metadata:

```php
DocumentChunk::create([
    'document_id' => $document->id,
    'chunk_text' => $chunkText,
    'chunk_index' => $chunkIndex,
    'metadata' => $metadata, // JSON: page, section info
]);
```

## Vector Embedding System

### 1. Embedding Generation

The `EmbeddingService` handles vector generation using Mistral AI:

```php
public function generateEmbedding(string $text): array
{
    $response = Embeddings::for([$text])
        ->generate('mistral', $this->model);

    // Extract embedding vector
    $embedding = [];
    if (property_exists($response, 'embeddings')) {
        $embeddings = $response->embeddings;
        if (is_array($embeddings) && !empty($embeddings)) {
            $embedding = $embeddings[0] ?? [];
        }
    }

    return $embedding;
}
```

**Caching Strategy:**

- **Cache Key**: MD5 hash of input text
- **Duration**: 30 days (embeddings are deterministic)
- **Storage**: Laravel Cache system

### 2. Batch Embedding Process

After document chunking, all chunks are processed for embeddings:

```php
// Generate embeddings for all chunks
foreach ($document->chunks as $chunk) {
    $embedding = $this->embeddingService->generateEmbedding($chunk->chunk_text);
    $chunk->update(['embedding' => json_encode($embedding)]);
}
```

**Database Storage:**

- Embeddings stored as JSON arrays in `document_chunks.embedding`
- Typical embedding dimension: 1536 (Mistral-Embed standard)

## Retrieval-Augmented Generation (RAG) Pipeline

### 1. Query Processing

When a user asks a question:

```php
// DocumentQuestionService@askQuestion
$searchResults = $this->vectorSearchService->searchDocument($document, $question);
```

### 2. Semantic Search

The `VectorSearchService` performs cosine similarity search:

```php
public function searchDocument(Document $document, string $query, int $topK = null): array
{
    // Generate query embedding
    $queryEmbedding = $this->embeddingService->generateEmbedding($query);

    // Get all chunks with embeddings for this document
    $chunks = $document->chunks()
        ->whereNotNull('embedding')
        ->get();

    $similarities = [];

    foreach ($chunks as $chunk) {
        $chunkEmbedding = json_decode($chunk->embedding, true);

        // Calculate cosine similarity
        $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);

        $similarities[] = [
            'chunk' => $chunk,
            'similarity' => $similarity
        ];
    }

    // Sort by similarity (descending) and return top-K
    usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

    return array_slice($similarities, 0, $topK ?? 5);
}
```

**Cosine Similarity Formula:**

```php
private function cosineSimilarity(array $a, array $b): float
{
    $dotProduct = 0;
    $normA = 0;
    $normB = 0;

    for ($i = 0; $i < count($a); $i++) {
        $dotProduct += $a[$i] * $b[$i];
        $normA += $a[$i] * $a[$i];
        $normB += $b[$i] * $b[$i];
    }

    return $dotProduct / (sqrt($normA) * sqrt($normB));
}
```

### 3. Context Building

Top-K relevant chunks are combined into context:

```php
protected function buildContext(array $searchResults): string
{
    $context = '';
    $chunkNumber = 1;

    foreach ($searchResults as $result) {
        $chunk = $result['chunk'];
        $metadata = $chunk->metadata ?? [];
        $page = $metadata['page'] ?? 'unknown';

        $context .= "[Chunk {$chunkNumber} - Page {$page}]\n";
        $context .= $chunk->chunk_text . "\n\n";
        $chunkNumber++;
    }

    return trim($context);
}
```

### 4. AI Agent Response Generation

The `DocumentQuestionAgent` generates the final answer:

```php
class DocumentQuestionAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return <<<INSTRUCTIONS
You are a document assistant. Rules:

1. ANSWER ONLY THE EXACT QUESTION - nothing more
2. Use ONLY the provided context
3. Maximum 1-2 sentences total
4. No introductions, explanations, or bullet points unless specifically asked
5. If not in context: "This information is not in the provided documents"
6. Use **bold** for key terms only
7. Be direct and factual

For "What is the main topic?" → Answer: "The main topic is **[topic]**."
INSTRUCTIONS;
    }

    public function model(): string
    {
        return 'mistral-large-latest';
    }

    public function provider(): string
    {
        return 'mistral';
    }
}
```

**Prompt Structure:**

```php
protected function buildPrompt(string $question, string $context): string
{
    return <<<PROMPT
Context from documents:
{$context}

Question: {$question}

Answer the question above using ONLY the context provided. Be concise. If the answer is not in the context, state that clearly.
PROMPT;
}
```

## Database Schema

### Documents Table

```sql
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    status ENUM('uploaded', 'processing', 'completed', 'failed') DEFAULT 'uploaded',
    total_chunks INT NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX(user_id),
    INDEX(status),
    INDEX(created_at)
);
```

### Document Chunks Table

```sql
CREATE TABLE document_chunks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    chunk_text LONGTEXT NOT NULL,
    chunk_index INT NOT NULL,
    start_char INT NULL,
    end_char INT NULL,
    embedding JSON NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    INDEX(document_id),
    INDEX(chunk_index)
);
```

### Document Questions Table

```sql
CREATE TABLE document_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    conversation_id VARCHAR(36) NULL,
    question TEXT NOT NULL,
    answer LONGTEXT NOT NULL,
    citations JSON NULL,
    retrieved_chunks JSON NULL,
    confidence DECIMAL(3,2) NULL,
    tokens_used INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX(user_id, created_at),
    INDEX(document_id, created_at),
    INDEX(conversation_id)
);
```

## API Endpoints

### Document Management

```php
// Upload document
POST /documents
Content-Type: multipart/form-data
{
    "file": <binary>
}

// List documents
GET /documents

// View document details
GET /documents/{id}

// Delete document
DELETE /documents/{id}

// Chat interface
GET /documents/{id}/chat
```

### Question & Answer

```php
// Ask question about specific document
POST /documents/{id}/ask
Content-Type: application/json
{
    "question": "What is the main topic?",
    "conversation_id": "uuid-string"
}

// Response
{
    "success": true,
    "data": {
        "id": 123,
        "question": "What is the main topic?",
        "answer": "The main topic is **document processing**.",
        "citations": [
            {
                "chunk_id": 45,
                "document_id": 12,
                "document_name": "guide.pdf",
                "page": 1,
                "similarity": 0.856
            }
        ],
        "confidence": 0.85,
        "confidence_percentage": 85,
        "is_high_confidence": true,
        "conversation_id": "uuid-string"
    }
}

// Ask across all documents
POST /documents/ask-all
Content-Type: application/json
{
    "question": "What is machine learning?",
    "conversation_id": "uuid-string"
}

// Get conversation history
GET /documents/{id}/history
```

## Chat Interface

### Frontend Architecture

The chat interface uses **Alpine.js** for reactivity:

```javascript
function chatInterface() {
    return {
        messages: [],
        question: "",
        loading: false,
        conversationId: null,

        async sendMessage() {
            // Add user message
            this.messages.push({
                role: "user",
                content: this.question,
            });

            // Send to backend
            const response = await fetch("/documents/{{ $document->id }}/ask", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: JSON.stringify({
                    question: this.question,
                    conversation_id: this.conversationId,
                }),
            });

            const data = await response.json();

            // Add assistant response
            if (data.success) {
                this.messages.push({
                    role: "assistant",
                    content: data.data.answer,
                    citations: data.data.citations,
                    confidence_percentage: data.data.confidence_percentage,
                    is_high_confidence: data.data.is_high_confidence,
                });
            }

            // Save to localStorage for persistence
            this.saveChatToStorage();
        },
    };
}
```

### Chat Persistence

**Hybrid Persistence Strategy:**

1. **Database Storage** - All Q&A pairs saved to `document_questions`
2. **localStorage Backup** - Frontend persistence for immediate loading
3. **Auto-sync** - Database loads on page load, localStorage provides instant UI

```javascript
// Load from database on page init
async loadChatHistory() {
    try {
        const response = await fetch('/documents/{{ $document->id }}/history');
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // Convert database records to chat messages
            this.messages = [];
            data.data.forEach(record => {
                this.messages.push(
                    { role: 'user', content: record.question },
                    {
                        role: 'assistant',
                        content: record.answer,
                        citations: record.citations,
                        confidence_percentage: record.confidence_percentage
                    }
                );
            });

            // Backup to localStorage
            localStorage.setItem('chat_{{ $document->id }}', JSON.stringify(this.messages));
        }
    } catch (error) {
        // Fallback to localStorage
        const saved = localStorage.getItem('chat_{{ $document->id }}');
        if (saved) {
            this.messages = JSON.parse(saved);
        }
    }
}
```

## Performance Optimizations

### 1. Embedding Caching

- **Strategy**: Cache embeddings by text hash for 30 days
- **Benefit**: Reduces API calls for repeated text processing
- **Implementation**: Laravel Cache with MD5 hash keys

### 2. Database Indexing

- **Compound Indexes**: `(user_id, created_at)`, `(document_id, created_at)`
- **Foreign Key Indexes**: Automatic indexing on relationships
- **Search Optimization**: Indexes on frequently queried columns

### 3. Chunking Strategy

- **Smart Boundaries**: Sentence-aware splitting prevents context loss
- **Optimal Size**: 800 characters balances context vs. granularity
- **Overlap**: 150 characters ensures continuity across chunks

### 4. Vector Search Efficiency

- **In-Memory Computation**: Cosine similarity calculated in PHP for small datasets
- **Top-K Limiting**: Only process top 5 results by default
- **Early Termination**: Skip processing if no embeddings available

## Configuration

### AI Configuration (`config/ai.php`)

```php
return [
    'default' => 'mistral',
    'default_model' => 'mistral-large-latest',
    'embedding_model' => 'mistral-embed',

    'providers' => [
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY'),
        ],
    ],

    // RAG Settings
    'chunk_size' => 800,
    'chunk_overlap' => 150,
    'top_k_chunks' => 5,

    // Caching
    'caching' => [
        'embeddings' => [
            'cache' => true,
            'ttl' => 60 * 60 * 24 * 30, // 30 days
        ],
    ],
];
```

### File Storage (`config/filesystems.php`)

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'visibility' => 'private',
    ],
],
```

## Security Considerations

### 1. File Upload Security

- **MIME Type Validation**: Restrict to safe file types
- **File Size Limits**: 10MB maximum upload size
- **UUID Naming**: Prevent directory traversal attacks
- **Private Storage**: Files not directly web-accessible

### 2. Input Sanitization

- **Question Validation**: 1000 character limit on questions
- **XSS Prevention**: Blade template escaping enabled
- **CSRF Protection**: Laravel CSRF tokens on all forms

### 3. AI Safety

- **Context Isolation**: Only document content used in prompts
- **Injection Prevention**: Structured prompt templates
- **Response Filtering**: Agent instructions prevent harmful outputs

## Deployment Pipeline

### 1. Environment Setup

```bash
# Install Dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Configure Environment
cp .env.example .env
php artisan key:generate

# Set up Database
php artisan migrate
php artisan db:seed

# Set Permissions
chmod -R 775 storage bootstrap/cache
```

### 2. Production Configuration

```env
# Environment
APP_ENV=production
APP_DEBUG=false

# AI Configuration
MISTRAL_API_KEY=your-mistral-api-key

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_project
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Performance Tuning

```bash
# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set up Queues (for async processing)
php artisan queue:work

# Enable OPcache (in php.ini)
opcache.enable=1
opcache.memory_consumption=128
```

## Monitoring & Analytics

### 1. Question Analytics

Track question patterns and performance:

- Most asked questions
- Average confidence scores
- Response times
- Document usage statistics

### 2. Performance Metrics

Monitor system performance:

- Embedding generation time
- Vector search latency
- AI response time
- Database query performance

### 3. Error Tracking

Log and monitor:

- Document processing failures
- AI API errors
- Vector search errors
- File upload issues

## Future Enhancements

### 1. Advanced Features

- **Multi-document Conversations**: Cross-reference multiple documents
- **Document Summarization**: Auto-generate document summaries
- **Question Suggestions**: AI-powered question recommendations
- **Export Functionality**: Export conversations to PDF/Word

### 2. Performance Improvements

- **Vector Database**: Migrate to specialized vector DB (Pinecone, Weaviate)
- **GPU Acceleration**: Local embedding generation
- **Streaming Responses**: Real-time AI response streaming
- **Caching Layer**: Redis for advanced caching strategies

### 3. Integration Options

- **API Authentication**: JWT/OAuth for API access
- **Webhook Integration**: External system notifications
- **Plugin System**: Extensible document processors
- **Multi-tenant Support**: Organization-based isolation

## Conclusion

This Document Q&A platform demonstrates a complete RAG implementation using modern Laravel and AI technologies. The architecture is designed for:

- **Scalability**: Modular services and efficient database design
- **Maintainability**: Clean code patterns and comprehensive documentation
- **Performance**: Optimized vector search and caching strategies
- **Security**: Safe file handling and AI prompt injection prevention
- **User Experience**: Responsive interface with real-time chat

The platform successfully combines traditional web development with cutting-edge AI capabilities, providing a robust foundation for document-based AI applications.
