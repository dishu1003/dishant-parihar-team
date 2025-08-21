-- Asclepius Wellness - Automated Income System
-- Database Seed Data
-- Version: 1.0
-- Author: Jules, Senior Full-Stack Engineer

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

--
-- 1. Users
--
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `city`, `role`, `password_hash`, `is_active`, `force_password_reset`) VALUES
(1, 'Dishant Parihar', 'admin@asclepius.local', '9876543210', 'Mumbai', 'admin', '$argon2id$v=19$m=65536,t=4,p=1$LkN0b2dVaDBxL2ZtY1hOaw$7R3TqfQk/Bko8pX3Uu3a/d5jY2X0Z6c2bX3Vq2w0Z1E', 1, 1), -- Password: ChangeMe@123
(2, 'Priya Sharma', 'priya.sharma@example.com', '9876543211', 'Delhi', 'member', '$argon2id$v=19$m=65536,t=4,p=1$U2FsdFNhbHRTYWx0U2E$5d7K/jRtY2bX3Vq2w0Z1E7R3TqfQk/Bko8pX3Uu3a/d', 1, 0), -- Generic password for samples
(3, 'Amit Singh', 'amit.singh@example.com', '9876543212', 'Bangalore', 'member', '$argon2id$v=19$m=65536,t=4,p=1$U2FsdFNhbHRTYWx0U2E$5d7K/jRtY2bX3Vq2w0Z1E7R3TqfQk/Bko8pX3Uu3a/d', 1, 0),
(4, 'Sunita Patel', 'sunita.patel@example.com', '9876543213', 'Ahmedabad', 'member', '$argon2id$v=19$m=65536,t=4,p=1$U2FsdFNhbHRTYWx0U2E$5d7K/jRtY2bX3Vq2w0Z1E7R3TqfQk/Bko8pX3Uu3a/d', 1, 0),
(5, 'Rajesh Kumar', 'rajesh.kumar@example.com', '9876543214', 'Chennai', 'member', '$argon2id$v=19$m=65536,t=4,p=1$U2FsdFNhbHRTYWx0U2E$5d7K/jRtY2bX3Vq2w0Z1E7R3TqfQk/Bko8pX3Uu3a/d', 0, 0), -- Inactive user
(6, 'Anjali Gupta', 'anjali.gupta@example.com', '9876543215', 'Kolkata', 'member', '$argon2id$v=19$m=65536,t=4,p=1$U2FsdFNhbHRTYWx0U2E$5d7K/jRtY2bX3Vq2w0Z1E7R3TqfQk/Bko8pX3Uu3a/d', 1, 0);

--
-- 2. User Profiles
--
INSERT INTO `user_profiles` (`user_id`, `age`, `occupation`, `bio`) VALUES
(1, 40, 'Founder', 'Leading Asclepius Wellness to new heights.'),
(2, 28, 'Software Developer', 'Excited to build a new income stream.'),
(3, 35, 'Sales Manager', 'Looking to apply my sales skills in a new venture.'),
(4, 45, 'Homemaker', 'Passionate about wellness and financial independence.'),
(5, 32, 'Marketing Executive', 'Exploring direct selling opportunities.'),
(6, 29, 'Teacher', 'Eager to learn about health products and business.');

--
-- 3. Tasks (Daily)
--
INSERT INTO `tasks` (`id`, `title`, `description`, `type`, `xp_reward`, `is_daily`) VALUES
(1, 'Prospecting: Add 2 New Leads', 'Add two new potential customers to your CRM.', 'prospecting', 20, 1),
(2, 'Follow-up: Contact 3 Warm Leads', 'Reach out to three existing warm leads.', 'followup', 15, 1),
(3, 'Learning: Complete a Module', 'Spend 15 minutes on a learning module.', 'learning', 10, 1),
(4, 'Personal: Plan Your Day', 'Outline your top 3 priorities for the day.', 'personal', 5, 1),
(5, 'Prospecting: Share a Product on Social Media', 'Use a pre-made template from Resources to post on WhatsApp or Facebook.', 'prospecting', 10, 1),
(6, 'Follow-up: Call 1 Hot Lead', 'Make a call to your top priority lead.', 'followup', 15, 1),
(7, 'Learning: Watch a Product Video', 'Watch one of the product training videos.', 'learning', 10, 1),
(8, 'Personal: Review Your Goals', 'Take 5 minutes to review your weekly and monthly goals.', 'personal', 5, 1),
(9, 'Prospecting: Ask for a Referral', 'Ask a happy customer or a new lead for a referral.', 'prospecting', 20, 1),
(10, 'Follow-up: Send a Thank You Note', 'Send a thank you message after a meeting.', 'followup', 10, 1);

--
-- 4. Leads (CRM)
--
INSERT INTO `leads` (`id`, `user_id`, `name`, `mobile`, `city`, `work`, `age`, `interest_level`, `notes`, `follow_up_date`, `status`) VALUES
(1, 2, 'Rohan Mehta', '8877665544', 'Delhi', 'IT Professional', 30, 'hot', 'Met at a tech park. Very interested in immunity boosters.', CURDATE() + INTERVAL 1 DAY, 'in_progress'),
(2, 2, 'Sonia Rao', '8877665545', 'Delhi', 'Yoga Instructor', 28, 'hot', 'Needs natural wellness products for her studio.', CURDATE(), 'in_progress'),
(3, 2, 'Vikram Batra', '8877665546', 'Noida', 'Student', 22, 'warm', 'Looking for a side income. Needs more info on the business plan.', CURDATE() + INTERVAL 2 DAY, 'new'),
(4, 3, 'Deepika Verma', '7766554433', 'Bangalore', 'Accountant', 35, 'warm', 'Cautious but interested in the financial benefits.', CURDATE() + INTERVAL 3 DAY, 'in_progress'),
(5, 3, 'Karan Desai', '7766554434', 'Bangalore', 'Graphic Designer', 29, 'cold', 'Not very responsive. Might need a different approach.', CURDATE() + INTERVAL 7 DAY, 'new'),
(6, 4, 'Meera Iyer', '6655443322', 'Ahmedabad', 'Housewife', 42, 'hot', 'Her family needs health supplements. Ready for a purchase.', CURDATE(), 'in_progress'),
(7, 4, 'Nisha Chauhan', '6655443323', 'Surat', 'Boutique Owner', 38, 'warm', 'Interested in products, not the business side yet.', CURDATE() + INTERVAL 1 DAY, 'in_progress'),
(8, 2, 'Aditya Narayan', '8877665547', 'Gurgaon', 'Architect', 40, 'warm', 'Wants to see product certifications.', CURDATE() + INTERVAL 4 DAY, 'new'),
(9, 3, 'Fatima Sheikh', '7766554435', 'Mysore', 'Librarian', 50, 'cold', 'Contacted once. Low interest.', CURDATE() + INTERVAL 10 DAY, 'new'),
(10, 4, 'Gaurav Singh', '6655443324', 'Vadodara', 'Banker', 33, 'converted', 'Joined the team last week.', NULL, 'converted'),
(11, 2, 'Pooja Hegde', '8877665548', 'Delhi', 'Marketing', 26, 'dropped', 'Decided not to pursue.', NULL, 'dropped'),
(12, 3, 'Suresh Reddy', '7766554436', 'Hyderabad', 'Real Estate Agent', 45, 'warm', 'Very busy, but sees potential. Follow up next week.', CURDATE() + INTERVAL 6 DAY, 'in_progress');

--
-- 5. Learning Modules
--
INSERT INTO `learning_modules` (`id`, `title`, `slug`, `category`, `summary`, `content_html`, `order_no`) VALUES
(1, 'Direct Selling Basics', 'direct-selling-basics', 'Direct Selling Basics', 'Understand the fundamentals of the direct selling industry.', '<p>Content for Direct Selling Basics.</p>', 1),
(2, 'Asclepius Company Profile', 'asclepius-company-profile', 'Asclepius Company Info', 'Learn about the history, vision, and mission of Asclepius Wellness.', '<p>Content for Asclepius Company Profile.</p>', 2),
(3, 'Our Flagship Products', 'flagship-products', 'Product Knowledge', 'A deep dive into our top-selling wellness products.', '<p>Content for Flagship Products.</p>', 3),
(4, 'The Business Plan Explained', 'business-plan-explained', 'Business Plan', 'Understand the compensation plan and how you can earn.', '<p>Content for The Business Plan Explained.</p>', 4),
(5, 'Effective Prospecting Techniques', 'effective-prospecting', 'Sales & Networking', 'Learn how to find and qualify new leads effectively.', '<p>Content for Effective Prospecting.</p>', 5),
(6, 'The Art of the Follow-Up', 'art-of-follow-up', 'Sales & Networking', 'Master the follow-up process to convert more leads.', '<p>Content for The Art of the Follow-Up.</p>', 6),
(7, 'Compliance and Ethics', 'compliance-and-ethics', 'Compliance & Ethics', 'Know the do''s and don''ts of the business.', '<p>Content for Compliance and Ethics.</p>', 7),
(8, 'Using Your Digital HQ', 'using-digital-hq', 'Direct Selling Basics', 'A complete guide to using this platform to grow your business.', '<p>Content for Using Your Digital HQ.</p>', 8),
(9, 'Product Knowledge: Nutrition', 'product-knowledge-nutrition', 'Product Knowledge', 'Details about our nutritional supplement range.', '<p>Content for Product Knowledge: Nutrition.</p>', 9),
(10, 'Closing the Sale', 'closing-the-sale', 'Sales & Networking', 'Techniques to confidently close sales and handle objections.', '<p>Content for Closing the Sale.</p>', 10);

--
-- 6. User Learning Progress (Sample)
--
INSERT INTO `user_learning` (`user_id`, `module_id`, `status`, `progress`) VALUES
(2, 1, 'completed', 100),
(2, 2, 'in_progress', 50),
(2, 3, 'not_started', 0),
(3, 1, 'completed', 100),
(3, 2, 'completed', 100),
(3, 4, 'in_progress', 25),
(4, 1, 'in_progress', 75);

--
-- 7. Resources
--
INSERT INTO `resources` (`id`, `title`, `category`, `file_path`, `mime_type`, `size`, `uploaded_by`) VALUES
(1, 'Product Brochure - Wellness', 'Brochures', '/uploads/resources/brochure_wellness.pdf', 'application/pdf', 1024000, 1),
(2, 'Business Plan Overview', 'Business Plan', '/uploads/resources/business_plan.pdf', 'application/pdf', 512000, 1),
(3, 'Social Media Post - Immunity', 'Social Media', '/uploads/resources/social_immunity.jpg', 'image/jpeg', 256000, 1),
(4, 'Call Script - New Prospect', 'Scripts', '/uploads/resources/script_new.txt', 'text/plain', 2048, 1),
(5, 'Call Script - Follow-up', 'Scripts', '/uploads/resources/script_followup.txt', 'text/plain', 2048, 1),
(6, 'Product Pricelist', 'Brochures', '/uploads/resources/pricelist.pdf', 'application/pdf', 307200, 1),
(7, 'Social Media Post - Income', 'Social Media', '/uploads/resources/social_income.jpg', 'image/jpeg', 256000, 1),
(8, 'Testimonials Document', 'Brochures', '/uploads/resources/testimonials.pdf', 'application/pdf', 716800, 1);

--
-- 8. Community Posts
--
INSERT INTO `community_posts` (`id`, `user_id`, `title`, `body`, `is_pinned`, `is_approved`) VALUES
(1, 1, 'Welcome to the New Digital HQ!', 'Welcome everyone! Use this platform to grow your business. Explore the learning hub and start adding your leads to the CRM.', 1, 1),
(2, 2, 'Question about Product A', 'Can someone share their experience with Product A? I have a new lead asking about it.', 0, 1),
(3, 3, 'My First Conversion!', 'Excited to share that I just converted my first lead using the follow-up script from the resources section! This system works!', 0, 1),
(4, 4, 'Best way to approach cold leads?', 'I have a few cold leads. What are your best tips for re-engaging them?', 0, 1),
(5, 1, 'Upcoming Webinar this Saturday', 'Don''t forget our weekly webinar this Saturday at 11 AM. Invite your prospects! Link will be shared on Friday.', 1, 1),
(6, 6, 'Feeling motivated!', 'Just joined and finished the first two learning modules. Feeling very motivated to start!', 0, 1),
(7, 2, 'Follow-up question for the group', 'How long do you usually wait before your first follow-up call?', 0, 1),
(8, 3, 'Great resource on sales', 'I found the "Closing the Sale" module particularly helpful. Highly recommend it to everyone.', 0, 1);


SET foreign_key_checks = 1;
