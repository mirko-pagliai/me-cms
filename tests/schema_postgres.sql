CREATE TABLE IF NOT EXISTS articles (
    id serial PRIMARY KEY,
    author_id INTEGER,
    title VARCHAR(255),
    body TEXT,
    published VARCHAR(1) DEFAULT 'N'
)
