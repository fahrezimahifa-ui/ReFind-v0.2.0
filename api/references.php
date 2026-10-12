<?php
require __DIR__ . '/bootstrap.php';
method('GET'); allowed_fields($_GET, []);
json_response(['categories' => rows('SELECT category_id AS id, category_name AS name FROM categories WHERE is_active = 1 ORDER BY display_order'),
    'locations' => rows("SELECT location_id AS id, location_code AS code, location_name AS name FROM campus_locations WHERE location_kind = 'AREA' AND is_active = 1 ORDER BY display_order"),
    'statuses' => rows('SELECT status_code AS code, status_name AS name FROM item_statuses ORDER BY display_order'),
    'filter_categories'=>rows('SELECT category_id AS id,category_name AS name,is_active FROM categories ORDER BY display_order'),
    'filter_locations'=>rows('SELECT location_id AS id,location_name AS name,location_kind FROM campus_locations WHERE is_active=1 ORDER BY location_kind,display_order'),
    'campus_today'=>date('Y-m-d'),'timezone'=>'Asia/Jakarta']);
