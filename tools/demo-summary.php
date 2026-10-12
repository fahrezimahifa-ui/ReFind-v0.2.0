<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../app/db.php';

function demo_summary(): array
{
    $counts=[];
    foreach (['users','items','claims','handovers','notifications','item_photos','claim_photos','item_matches','item_status_history'] as $table) {
        $counts[$table]=(int)row('SELECT COUNT(*) AS n FROM '.$table)['n'];
    }
    return ['database'=>app_config()['db_name'], 'counts'=>$counts,
        'accounts'=>rows('SELECT user_id,full_name,email,role,is_active FROM users ORDER BY user_id'),
        'report_types'=>rows('SELECT item_type,COUNT(*) AS count FROM items GROUP BY item_type'),
        'report_states'=>rows('SELECT s.status_code,COUNT(*) AS count FROM items i JOIN item_statuses s ON s.status_id=i.status_id GROUP BY s.status_code'),
        'claim_states'=>rows('SELECT claim_status,COUNT(*) AS count FROM claims GROUP BY claim_status'),
        'pickup_states'=>rows('SELECT handover_status,COUNT(*) AS count FROM handovers GROUP BY handover_status'),
        'photo_kinds'=>rows('SELECT photo_kind,COUNT(*) AS count FROM item_photos GROUP BY photo_kind'),
        'match_states'=>rows('SELECT match_status,COUNT(*) AS count FROM item_matches GROUP BY match_status'),
        'reports'=>rows('SELECT i.item_id,i.item_type,i.title,i.event_date,i.event_time,u.full_name AS reporter,s.status_code FROM items i JOIN users u ON u.user_id=i.reporter_user_id JOIN item_statuses s ON s.status_id=i.status_id ORDER BY i.item_id')];
}

if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    $summary=demo_summary();
    $json=json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    if(in_array('--evidence',$argv,true)) {
        if(app_config()['db_name']!=='refind_db_0_2_0'||app_config()['db_port']!==3306)throw new RuntimeException('Main evidence requires the main demo connection.');
        $directory=app_root().'/docs/verification/b06';if(!is_dir($directory))throw new RuntimeException('Evidence directory unavailable.');
        file_put_contents($directory.'/dataset.json',$json,LOCK_EX);
        echo json_encode(['evidence'=>'docs/verification/b06/dataset.json','counts'=>$summary['counts']],JSON_PRETTY_PRINT).PHP_EOL;
    } else echo $json;
}
