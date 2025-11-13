#!/usr/bin/env python3
import sys
import json
import re

def analyze_sentiment(text):
    """
    Enhanced sentiment analysis based on keywords with confidence scoring.
    Returns: positive, negative, or neutral with detailed metrics
    """
    text_lower = text.lower()
    
    # Expanded word lists
    positive_words = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 
                      'love', 'happy', 'best', 'awesome', 'brilliant', 'perfect', 'outstanding',
                      'superb', 'marvelous', 'delightful', 'pleased', 'satisfied', 'joyful',
                      'cheerful', 'glad', 'content', 'thrilled', 'excited', 'grateful']
    negative_words = ['bad', 'terrible', 'awful', 'horrible', 'worst', 'hate', 
                      'sad', 'poor', 'disappointing', 'useless', 'boring', 'angry',
                      'frustrated', 'annoyed', 'upset', 'disgusting', 'pathetic', 'miserable',
                      'depressed', 'unhappy', 'disappointed', 'disgusted', 'furious']
    
    # Count word occurrences (using word boundaries for better matching)
    words = re.findall(r'\b\w+\b', text_lower)
    positive_count = sum(1 for word in words if word in positive_words)
    negative_count = sum(1 for word in words if word in negative_words)
    
    total_sentiment_words = positive_count + negative_count
    
    # Calculate sentiment score and confidence
    if positive_count > negative_count:
        sentiment = "positive"
        raw_score = positive_count - negative_count
        if total_sentiment_words > 0:
            confidence = (positive_count / total_sentiment_words) * 100
        else:
            confidence = 0
    elif negative_count > positive_count:
        sentiment = "negative"
        raw_score = negative_count - positive_count
        if total_sentiment_words > 0:
            confidence = (negative_count / total_sentiment_words) * 100
        else:
            confidence = 0
    else:
        sentiment = "neutral"
        raw_score = 0
        confidence = 50  # Neutral confidence
    
    # Calculate overall sentiment percentage (normalized to 0-100 scale)
    if total_sentiment_words > 0:
        sentiment_percentage = ((positive_count - negative_count) / total_sentiment_words) * 50 + 50
        sentiment_percentage = max(0, min(100, sentiment_percentage))  # Clamp between 0-100
    else:
        sentiment_percentage = 50  # Neutral
    
    return {
        "sentiment": sentiment,
        "score": raw_score,
        "confidence": round(confidence, 1),
        "sentiment_percentage": round(sentiment_percentage, 1),
        "positive_words_found": positive_count,
        "negative_words_found": negative_count,
        "text_length": len(text),
        "character_count": len(text),
        "word_count": len(words),
        "sentiment_words_total": total_sentiment_words
    }

if __name__ == "__main__":
    # Try to read from stdin first (more reliable cross-platform)
    # If stdin is empty, fall back to command line argument
    text = None
    
    if not sys.stdin.isatty():
        # Read from stdin
        text = sys.stdin.read().strip()
    
    if not text and len(sys.argv) >= 2:
        # Fall back to command line argument
        text = sys.argv[1]
    
    if not text:
        print(json.dumps({"error": "No text provided"}))
        sys.exit(1)
    
    result = analyze_sentiment(text)
    print(json.dumps(result))