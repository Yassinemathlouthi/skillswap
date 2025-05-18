-- Add the missing columns to the faq table
ALTER TABLE faq ADD display_order INT NOT NULL DEFAULT 0;
ALTER TABLE faq ADD is_published TINYINT(1) NOT NULL DEFAULT 1; 