# 📚 Document Q&A Homework Helper

An AI-powered homework helper that allows students to upload documents (PDF, DOCX, TXT) and ask questions about their content using **Mistral AI** and **RAG (Retrieval Augmented Generation)**.

Built with **Laravel 13**, **Tailwind CSS**, **Alpine.js**, and the **TALL stack** principles.

## ✨ Features

- **Document Upload**: Support for PDF, DOCX, and TXT files (up to 10MB)
- **Intelligent Chunking**: Documents are split into overlapping chunks for better context
- **Vector Embeddings**: Uses Mistral AI to generate embeddings for semantic search
- **RAG Pipeline**: Retrieves relevant chunks and generates grounded answers
- **Chat Interface**: Interactive Q&A with real-time responses
- **Citations**: Every answer includes source references with page numbers
- **Confidence Scores**: Visual indicators showing answer confidence level
- **Multi-turn Conversations**: Maintains context across multiple questions
- **Document Management**: Upload, view, and delete documents

## 🚀 Installation

### 1. Install Composer Dependencies

The system requires PDF and DOCX parsing libraries:

```bash
composer require smalot/pdfparser phpoffice/phpword
```

### 2. Configure Mistral AI

Your `.env` file should already have `MISTRAL_API_KEY` set. Add these additional settings:

```env
# Mistral AI Configuration
MISTRAL_API_KEY=your_key_here
MISTRAL_MODEL=mistral-large-latest
MISTRAL_EMBEDDING_MODEL=mistral-embed

# AI Configuration
AI_DEFAULT_PROVIDER=mistral
AI_EMBEDDING_PROVIDER=mistral
CACHE_EMBEDDINGS=true

# Document Processing
CHUNK_SIZE=800
CHUNK_OVERLAP=150
TOP_K_CHUNKS=5

# File Limits
MAX_DOCUMENT_SIZE_MB=10
```

### 3. Run Migrations

Migrations have already been run, but if you need to run them again:

```bash
php artisan migrate
```

### 4. Create Storage Link

```bash
php artisan storage:link
```

### 5. Build Frontend Assets

```bash
npm install
npm run build
```

Or for development:

```bash
npm run dev
```

### 6. Start the Development Server

```bash
php artisan serve
```

Visit http://localhost:8000 (or your configured URL)

## 📖 Usage

### Uploading Documents

1. Navigate to the home page
2. Click "Upload New Document"
3. Select a PDF, DOCX, or TXT file
4. Wait for processing (automatic)

### Asking Questions

1. Click "Ask Questions" on any processed document
2. Type your question in the chat interface
3. Get instant answers with citations and confidence scores

### Understanding Results

- **✓ Ready**: Document is processed and ready for questions
- **⏳ Processing**: Document is being chunked and embedded
- **✗ Failed**: Processing error (check error message)

**Confidence Scores**:

- **Green (75%+)**: High confidence - answer well-supported by document
- **Yellow (<75%)**: Lower confidence - answer may be less certain

## 🏗️ Architecture

### Backend Services

1. **DocumentParser** (PDF/DOCX/TXT)
    - Extracts text from various file formats
    - Preserves metadata (page numbers, sections)

2. **DocumentProcessorService**
    - Sentence-aware text chunking
    - Configurable chunk size and overlap
    - Stores chunks in database

3. **EmbeddingService**
    - Generates embeddings via Mistral AI
    - Caches embeddings for efficiency
    - Cosine similarity calculation

4. **VectorSearchService**
    - Searches document chunks by semantic similarity
    - Returns top-K most relevant chunks
    - Supports single-document and multi-document search

5. **DocumentQuestionService**
    - Orchestrates RAG pipeline
    - Builds context from retrieved chunks
    - Generates answers with Mistral AI
    - Tracks confidence and citations

### Database Schema

**documents**: Stores uploaded files and processing status

- `id`, `user_id`, `filename`, `original_name`, `file_path`, `file_type`, `file_size`
- `total_chunks`, `status`, `error_message`, `timestamps`

**document_chunks**: Stores text chunks with embeddings

- `id`, `document_id`, `chunk_text`, `chunk_index`
- `start_char`, `end_char`, `embedding`, `metadata`, `timestamps`

**document_questions**: Stores Q&A history

- `id`, `document_id`, `user_id`, `conversation_id`
- `question`, `answer`, `citations`, `retrieved_chunks`
- `confidence`, `tokens_used`, `timestamps`

### Frontend Components

- **documents/index.blade.php**: Document list and upload interface
- **documents/chat.blade.php**: Interactive Q&A chat with Alpine.js
- **layouts/app.blade.php**: Main layout with Tailwind styling

## 🔧 Configuration

### Chunking Strategy

Edit `config/ai.php` or `.env`:

```php
'chunk_size' => 800,        // Characters per chunk
'chunk_overlap' => 150,     // Overlap to prevent info loss
'top_k_chunks' => 5,        // Number of chunks to retrieve
```

### Models

```php
'default_model' => 'mistral-large-latest',   // For answer generation
'embedding_model' => 'mistral-embed',         // For embeddings
```

### Caching

Enable embedding caching to reduce API costs:

```php
'caching' => [
    'embeddings' => [
        'cache' => true,
        'store' => 'database',
    ],
],
```

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Or with Pest:

```bash
./vendor/bin/pest
```

## 📊 Cost Optimization

- **Embeddings are cached** to avoid regenerating for the same text
- **Batch processing** minimizes API calls
- **Use mistral-small** for embeddings (cheaper than mistral-large)
- **Implement user quotas** to control costs (e.g., 100 questions/day)

## 🔒 Security

- File type validation (PDF, DOCX, TXT only)
- File size limits (10MB default)
- CSRF protection (built into Laravel)
- Input sanitization before sending to AI
- Isolated user documents (no cross-user access)

## 🐛 Troubleshooting

### "PDF parser not installed"

```bash
composer require smalot/pdfparser
```

### "DOCX parser not installed"

```bash
composer require phpoffice/phpword
```

### "Failed to generate embedding"

Check your `MISTRAL_API_KEY` in `.env` and ensure you have API credits.

### Documents stuck in "Processing"

Check `storage/logs/laravel.log` for errors. Common issues:

- Invalid PDF format
- Corrupted file
- API rate limits

### Migrations failed

```bash
php artisan migrate:fresh
```

## 📈 Future Enhancements

- [ ] Queue-based document processing (for larger files)
- [ ] Multi-document querying (search across all user documents)
- [ ] Export conversations as PDF
- [ ] Document preview/viewer
- [ ] Advanced filters (by date, file type)
- [ ] User authentication and quotas
- [ ] Admin dashboard for monitoring

## 📝 API Endpoints

### Web Routes

- `GET /documents` - List documents
- `POST /documents` - Upload document
- `GET /documents/{id}/chat` - Chat interface
- `DELETE /documents/{id}` - Delete document

### AJAX Endpoints

- `POST /documents/{id}/ask` - Ask question about specific document
- `POST /documents/ask-all` - Ask across all user documents

## 🤝 Contributing

This project was built as a homework helper for educational purposes. Feel free to extend it!

## 📄 License

MIT License

## 🙏 Acknowledgments

- **Mistral AI** for powerful language models
- **Laravel AI** package for seamless integration
- **Tailwind CSS** for beautiful styling
- **Alpine.js** for reactive UI
