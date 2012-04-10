insert into offers (`id`, `title`, `description`, `started`, `ended`,
                    `autostart`, `autoend`, `coupon_terms`, `total_quantity`,
                    `coupon_count`, `tags`, `offer_category_id`,
                    `offer_type_id`, `company_id`, `offer_state_id`)
       values   (1, 'prosfora 1', 'random description is random', NOW(),
                    '2014-01-01 10:00:00', NULL, NULL, '',
                    1000, -1, 'tag1 tag2 tag3', 1, 1, 1, 1);
