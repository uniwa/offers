insert into offers (`id`, `title`, `description`, `starting`, `ending`,
                    `expiration_date`, `is_active`, `total_quantity`,
                    `current_quantity`, `tags`, `is_draft`,
                    `offer_category_id`, `offer_type_id`, `company_id`)
       values   (1, 'prosfora 1', 'random description is random', NOW(), '2014-01-01 10:00:00',
                 '2016-01-01 10:00:00', 1, 1000, 12, 'tag1 tag2 tag3', 0,
                 1, 1, 1);
