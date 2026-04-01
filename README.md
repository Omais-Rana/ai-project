# 📚 AI-Powered Homework Helper

An **intelligent academic assistant** that helps students upload documents (PDF, DOCX, TXT) and leverage **advanced AI study tools** powered by **Mistral AI** and **RAG (Retrieval Augmented Generation)**.

Built with the **TALL stack**.

## ✨ Features

### 🤖 Intelligent Document Q&A

- **Document Upload**: Support for PDF, DOCX, and TXT files (up to 10MB)
- **Smart Processing**: Documents are chunked for optimal AI analysis
- **Vector Embeddings**: Uses Mistral AI for semantic search
- **RAG Pipeline**: Retrieves relevant content and generates accurate answers
- **Interactive Chat**: Real-time Q&A with conversation context
- **Smart Citations**: Source attribution with confidence scores

### 📚 AI Study Tools

#### 🃏 Flashcard Generator

- **Auto-Generation**: Creates 15-30 interactive flashcards from document content
- **Difficulty Levels**: Easy, medium, and hard cards for progressive learning
- **Interactive Interface**: 3D flip animations with keyboard navigation
- **Export Options**:
    - **Anki CSV**: Import directly into Anki for spaced repetition
    - **JSON**: Compatible with other flashcard apps

#### ✅ Quiz Generator

- **Mixed Question Types**: Multiple choice, true/false, and fill-in-the-blank
- **Smart Difficulty**: Balanced distribution across difficulty levels
- **Instant Feedback**: Immediate answer checking with explanations
- **10-15 Questions**: Optimal length for effective knowledge assessment

#### 📝 Document Summarizer

- **Three Summary Modes**:
    - **TL;DR**: Ultra-brief 2-3 sentence overview
    - **Brief**: Concise 1-2 paragraph summary (100-200 words)
    - **Detailed**: Comprehensive 3-5 paragraph analysis (300-500 words)
- **Context-Aware**: Preserves main ideas and key terminology
- **Student-Friendly**: Clear, accessible language

### 🎨 Modern User Experience

- **Elegant Design**: Gradient-based modern interface
- **Loading Indicators**: Visual feedback during file uploads and AI generation
- **Responsive Layout**: Works perfectly on desktop and mobile
- **Smooth Animations**: Professional transitions and hover effects

## 🚀 Installation & Setup

### Prerequisites

- **PHP 8.1+** with extensions: `mbstring`, `fileinfo`, `pdo_mysql`
- **Composer** (PHP package manager)
- **Node.js & npm** (for frontend assets)
- **MySQL** database
- **Mistral AI API key** (sign up at [console.mistral.ai](https://console.mistral.ai))

### 1. Clone and Install

```bash
# Clone the repository
git clone <your-repo-url> ai-homework-helper
cd ai-homework-helper

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit your `.env` file with the following settings:

```env
# Application
APP_NAME="AI Homework Helper"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_homework_helper
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mistral AI Configuration
MISTRAL_API_KEY=your_mistral_api_key_here
MISTRAL_MODEL=mistral-large-latest
MISTRAL_EMBEDDING_MODEL=mistral-embed

# AI Settings
AI_DEFAULT_PROVIDER=mistral
AI_EMBEDDING_PROVIDER=mistral
AI_API_TIMEOUT=180
MISTRAL_TIMEOUT=180

# Document Processing
CHUNK_SIZE=800
CHUNK_OVERLAP=150
TOP_K_CHUNKS=5

# File Upload Limits
MAX_UPLOAD_SIZE=10240  # 10MB in KB
```

### 3. Database Setup

```bash
# Create database (MySQL) or use the ai.sql file given in the database folder and import it
mysql -u root -p
CREATE DATABASE ai;
exit

# Run migrations to create all tables
php artisan migrate
```

### 4. Storage Configuration

```bash
# Create storage link for file uploads
php artisan storage:link

# Set proper permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

### 5. Build Frontend Assets

```bash
# For production
npm run build

# OR for development (with watching)
npm run dev
```

### 6. Start the Application

```bash
# Development server
php artisan serve
```

Visit **http://localhost:8000** to access your AI homework helper!

## 📖 How to Use

### 1. Upload Documents

1. Click **"Upload New Document"** on the homepage
2. Select a PDF, DOCX, or TXT file (max 10MB)
3. Wait for processing (typically 10-30 seconds)
4. Document appears in your library when ready

### 2. Chat with Documents

1. Click **"💬 Ask Questions"** on any processed document
2. Type questions about the document content
3. Get instant AI-powered answers with source citations
4. Ask follow-up questions - the AI maintains conversation context

### 3. Generate Study Materials

Click **"📚 Study Materials"** on any document to access:

#### 🃏 Flashcards

- Click "Flashcards" → Auto-generates 15-30 cards
- Use arrow keys or click to flip cards
- Filter by difficulty level
- Export to Anki or JSON format

#### ✅ Quiz

- Click "Quiz" → Generates 10-15 mixed questions
- Choose difficulty or use "Mixed" mode
- Get instant feedback with explanations
- Retake with different questions anytime

#### 📝 Summary

- Click "Summary" → Choose your preferred mode
- **TL;DR**: Quick 2-3 sentence overview
- **Brief**: Balanced 1-2 paragraph summary
- **Detailed**: Comprehensive multi-paragraph analysis

## 🏗️ Technical Architecture

### Backend Services

- **DocumentProcessorService**: Handles file parsing and chunking
- **VectorSearchService**: Manages embeddings and similarity search
- **DocumentQuestionService**: Orchestrates RAG pipeline for Q&A
- **FlashcardService**: Generates and manages flashcard data
- **QuizService**: Creates assessments with multiple question types
- **SummaryService**: Produces summaries at different detail levels

### AI Agents

- **DocumentQuestionAgent**: Specialized for conversational Q&A
- **FlashcardAgent**: Optimized for active recall and memorization
- **QuizAgent**: Expert at educational assessment generation
- **SummaryAgent**: Condenses content at multiple granularity levels

### Database Schema

- **documents**: File metadata and processing status
- **document_chunks**: Text segments with embeddings
- **document_questions**: Q&A conversation history
- **flashcards**: Generated flashcard pairs with difficulty ratings
- **quizzes**: Quiz questions and answer data
- **summaries**: Cached summaries by mode (TL;DR, Brief, Detailed)

### Frontend Stack

- **Laravel Blade**: Server-side templating
- **Tailwind CSS**: Utility-first styling with custom gradients
- **Alpine.js**: Reactive JavaScript behavior
- **Modern UX**: Loading overlays, smooth animations, responsive design

## 🎯 API Endpoints

### Document Management

- `GET /documents` - List all documents with upload interface
- `POST /documents` - Upload and process new document
- `GET /documents/{id}` - View document details and study materials
- `DELETE /documents/{id}` - Delete document and all associated data
- `GET /documents/{id}/chat` - Interactive Q&A chat interface

### Q&A System

- `POST /documents/{id}/ask` - Ask questions about specific document
- `GET /documents/{id}/history` - Retrieve conversation history

### Study Materials

#### Flashcards

- `GET /documents/{id}/flashcards` - View/generate flashcard interface
- `POST /documents/{id}/flashcards/generate` - Create new flashcards
- `GET /documents/{id}/flashcards/export/anki` - Export as Anki CSV
- `GET /documents/{id}/flashcards/export/json` - Export as JSON

#### Quiz

- `GET /documents/{id}/quiz` - View/generate quiz interface (param: `difficulty`)
- `POST /documents/{id}/quiz/generate` - Create new quiz

#### Summary

- `GET /documents/{id}/summary` - View/generate summary (param: `mode`)
- `POST /documents/{id}/summary/generate` - Create new summary

## 🔧 Configuration

### AI Settings (`config/ai.php`)

```php
// Model Configuration
'default_model' => 'mistral-large-latest',
'embedding_model' => 'mistral-embed',

// Document Processing
'chunk_size' => 800,        // Characters per chunk
'chunk_overlap' => 150,     // Overlap between chunks
'top_k_chunks' => 5,        // Chunks retrieved for context

// API Timeouts
'api_timeout' => 180,       // 3 minutes for AI requests
'connection_timeout' => 30, // Connection timeout
```

### File Upload Limits

Modify in `.env`:

```env
MAX_UPLOAD_SIZE=10240  # 10MB in kilobytes
```

Or in `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## 💰 Cost Optimization

### Smart Caching Strategy

- **All study materials cached** - generate once, use forever
- **Embeddings cached** - no re-processing of same content
- **Conversation context** - efficient multi-turn discussions

### Estimated Costs (Mistral AI Pricing)

- **Document Processing**: $0.01-0.05 per document (one-time)
- **Q&A**: $0.001-0.005 per question
- **Flashcards**: $0.03-0.07 per generation (cached)
- **Quiz**: $0.04-0.08 per generation (cached)
- **Summary**: $0.01-0.03 per mode (cached)

💡 **Pro Tip**: Study materials are permanently cached. Only regenerate if you want different content!

## 🔒 Security Features

- **File Type Validation**: Only PDF, DOCX, TXT allowed
- **Size Limits**: Configurable upload size restrictions
- **Input Sanitization**: All user input cleaned before AI processing
- **CSRF Protection**: Built-in Laravel security
- **Storage Security**: Files stored outside web root

## 🐛 Troubleshooting

### Common Setup Issues

**"PDF parser not installed"**

```bash
composer require smalot/pdfparser
```

**"DOCX parser not installed"**

```bash
composer require phpoffice/phpword
```

**"Failed to generate embedding"**

- Check `MISTRAL_API_KEY` in `.env`
- Verify API credits in Mistral console
- Check internet connectivity

**"Maximum execution time exceeded"**

- Large documents may take 3-5 minutes to process
- Check PHP `max_execution_time` setting
- The app automatically increases timeout limits

**Documents stuck in "Processing"**

- Check `storage/logs/laravel.log` for errors
- Common causes: corrupted files, API rate limits
- Try re-uploading the document

**Study materials not generating**

- Ensure document is fully processed first
- Check Laravel logs for AI API errors
- Verify Mistral API key has sufficient credits

### Performance Tips

- Use **SSD storage** for faster file processing
- **Increase PHP memory limit** for large documents
- **Enable Redis/Memcached** for better caching
- **Use queue workers** for background processing (advanced)

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up SSL certificate
- [ ] Configure file permissions (755/644)
- [ ] Set up proper backup strategy
- [ ] Monitor API usage and costs

### Server Requirements

- **PHP 8.1+** with required extensions
- **MySQL 8.0+** or **PostgreSQL 13+**
- **Nginx** or **Apache** web server
- **SSL certificate** (recommended)
- **Minimum 2GB RAM** (4GB recommended for larger documents)

## 🤝 Contributing

This project was built as an educational tool. Contributions welcome!

### Development Setup

```bash
# Clone and setup as above, then:

# Install development dependencies
composer install --dev
npm install

# Run tests
php artisan test

# Watch for changes
npm run dev
```

## 📄 License

MIT License - feel free to modify and extend for educational purposes!

## 🙏 Acknowledgments

- **Mistral AI** for powerful language models
- **Laravel AI** package for seamless integration
- **Tailwind CSS** for beautiful modern styling
- **Alpine.js** for reactive user interfaces

---

**Ready to supercharge your studying? Upload your first document and let AI help you learn!** 🚀📚
