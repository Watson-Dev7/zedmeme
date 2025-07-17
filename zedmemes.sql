-- Users table with enhanced security and profile features
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Categories table for better organization
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7), -- For hex color codes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_categories_name (name),
    INDEX idx_categories_is_active (is_active)
);

-- Tags table for flexible tagging system
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tags_name (name),
    INDEX idx_tags_usage_count (usage_count)
);

-- Enhanced memes table
CREATE TABLE memes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    alt_text VARCHAR(500), -- For accessibility
    file_size INT, -- in bytes
    mime_type VARCHAR(100),
    width INT,
    height INT,
    is_public BOOLEAN DEFAULT TRUE,
    is_nsfw BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'archived', 'deleted') DEFAULT 'published',
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    -- Indexes for performance
    INDEX idx_memes_user_id (user_id),
    INDEX idx_memes_category_id (category_id),
    INDEX idx_memes_created_at (created_at),
    INDEX idx_memes_status (status),
    INDEX idx_memes_is_public (is_public),
    INDEX idx_memes_is_featured (is_featured),
    INDEX idx_memes_view_count (view_count),
    
    -- Full-text search index
    FULLTEXT idx_memes_search (title, description)
);

-- Junction table for meme tags (many-to-many relationship)
CREATE TABLE meme_tags (
    meme_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (meme_id, tag_id),
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    
    INDEX idx_meme_tags_meme_id (meme_id),
    INDEX idx_meme_tags_tag_id (tag_id)
);

-- Enhanced reactions table with better structure
CREATE TABLE reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meme_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('like', 'dislike', 'upvote', 'downvote', 'love', 'laugh', 'wow', 'angry') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Unique constraint to prevent duplicate reactions of same type
    UNIQUE KEY unique_user_meme_reaction (meme_id, user_id, type),
    
    -- Indexes for performance
    INDEX idx_reactions_meme_id (meme_id),
    INDEX idx_reactions_user_id (user_id),
    INDEX idx_reactions_type (type),
    INDEX idx_reactions_created_at (created_at)
);

-- Comments table for user engagement
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meme_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL, -- For nested comments/replies
    content TEXT NOT NULL,
    is_edited BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    
    INDEX idx_comments_meme_id (meme_id),
    INDEX idx_comments_user_id (user_id),
    INDEX idx_comments_parent_id (parent_id),
    INDEX idx_comments_created_at (created_at)
);

-- User follows table for social features
CREATE TABLE user_follows (
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Prevent self-following
    CONSTRAINT chk_no_self_follow CHECK (follower_id != following_id),
    
    INDEX idx_follows_follower (follower_id),
    INDEX idx_follows_following (following_id)
);

-- Reports table for content moderation
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    meme_id INT,
    comment_id INT,
    reason ENUM('spam', 'inappropriate', 'copyright', 'harassment', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    
    -- Ensure at least one content item is being reported
    CONSTRAINT chk_report_content CHECK (meme_id IS NOT NULL OR comment_id IS NOT NULL),
    
    INDEX idx_reports_status (status),
    INDEX idx_reports_meme_id (meme_id),
    INDEX idx_reports_comment_id (comment_id),
    INDEX idx_reports_created_at (created_at)
);

-- Create a view for meme statistics (computed columns)
CREATE VIEW meme_stats AS
SELECT 
    m.id,
    m.title,
    m.user_id,
    m.view_count,
    m.download_count,
    COUNT(DISTINCT CASE WHEN r.type = 'like' THEN r.user_id END) as like_count,
    COUNT(DISTINCT CASE WHEN r.type = 'upvote' THEN r.user_id END) as upvote_count,
    COUNT(DISTINCT CASE WHEN r.type = 'dislike' THEN r.user_id END) as dislike_count,
    COUNT(DISTINCT CASE WHEN r.type = 'downvote' THEN r.user_id END) as downvote_count,
    COUNT(DISTINCT c.id) as comment_count,
    (COUNT(DISTINCT CASE WHEN r.type IN ('like', 'upvote') THEN r.user_id END) - 
     COUNT(DISTINCT CASE WHEN r.type IN ('dislike', 'downvote') THEN r.user_id END)) as net_score
FROM memes m
LEFT JOIN reactions r ON m.id = r.meme_id
LEFT JOIN comments c ON m.id = c.meme_id AND c.is_deleted = FALSE
WHERE m.status = 'published'
GROUP BY m.id, m.title, m.user_id, m.view_count, m.download_count;

-- Triggers to update tag usage count
DELIMITER //
CREATE TRIGGER update_tag_usage_after_insert 
AFTER INSERT ON meme_tags
FOR EACH ROW
BEGIN
    UPDATE tags SET usage_count = usage_count + 1 WHERE id = NEW.tag_id;
END//

CREATE TRIGGER update_tag_usage_after_delete 
AFTER DELETE ON meme_tags
FOR EACH ROW
BEGIN
    UPDATE tags SET usage_count = usage_count - 1 WHERE id = OLD.tag_id;
END//
DELIMITER ;

<?php
// In signup.php catch block
echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
]);