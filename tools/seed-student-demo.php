<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') {http_response_code(403);exit;}
require __DIR__.'/../app/reports.php';
date_default_timezone_set('Asia/Jakarta');
try {
    $receiptPath=storage_dir('setup').'/student-demo.json';
    $receipt=is_file($receiptPath)?json_decode(file_get_contents($receiptPath),true,64,JSON_THROW_ON_ERROR):['database'=>app_config()['db_name'],'items'=>[]];
    if ($receipt['database']!==app_config()['db_name']) throw new RuntimeException('Demo receipt belongs to another database.');
    if (!$receipt['items']&&(int)row('SELECT COUNT(*) AS n FROM items')['n']!==0) throw new RuntimeException('Existing reports found; initial demo seed stopped to avoid duplicating user work.');
    $examples=[
      'headphones-lost'=>['email'=>'student-a@refind.test','type'=>'LOST','title'=>'Demo · AKG headphones lost','description'=>'Synthetic classroom example: black over-ear headphones with gold rings and a twin-wire headband. Last seen in Gedung C.','category'=>1,'photo'=>'headphones-overview.jpg','proof'=>'headphones-earcup.jpg','clue'=>'Demo clue: the gold badge says 600 ohms and MADE IN AUSTRIA.'],
      'headphones-found'=>['email'=>'student-b@refind.test','type'=>'FOUND','title'=>'Demo · Headphones found near Gedung C lift','description'=>'Synthetic classroom example: black AKG headphones with gold rings and a twin-wire headband, found near the Gedung C lift.','category'=>1,'photo'=>'headphones-headband.jpg','proof'=>null,'clue'=>'Demo clue: the gold badge says 600 ohms and MADE IN AUSTRIA.'],
      'umbrella-found'=>['email'=>'student-b@refind.test','type'=>'FOUND','title'=>'Demo · Brown folding umbrella','description'=>'Synthetic classroom example: a compact brown folding umbrella with a tan handle, found near the campus field.','category'=>8,'photo'=>'umbrella-overview.jpg','proof'=>null,'clue'=>'Demo clue: a pale trim runs along the lower edge of the folded fabric.'],
    ];
    $added=0;
    foreach ($examples as $key=>$example) {
        $user=row('SELECT user_id,role FROM users WHERE email=? AND is_active=1',[$example['email']]);
        if (!$user||$user['role']!=='STUDENT') throw new RuntimeException('Demo Student account unavailable.');
        if (isset($receipt['items'][$key])) {
            $id=(int)$receipt['items'][$key];$existing=row('SELECT item_type,reporter_user_id FROM items WHERE item_id=?',[$id]);
            if (!$existing) continue; // A deliberately deleted example is not silently recreated.
            if ($existing['item_type']!==$example['type']||(string)$existing['reporter_user_id']!==(string)$user['user_id']) throw new RuntimeException('Demo bookmark identity differs.');
        } else {
            $result=mutate_report($user,['action'=>'create','type'=>$example['type'],'title'=>$example['title'],'description'=>$example['description'],'category_id'=>(string)$example['category'],'location_id'=>$key==='umbrella-found'?'40':'37','location_detail'=>'Demonstration location only','date'=>date('Y-m-d'),'time'=>'','clue'=>$example['clue']]);
            $id=(int)$result['id'];$receipt['items'][$key]=$id;$added++;
            file_put_contents($receiptPath,json_encode($receipt,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),LOCK_EX);
        }
        if ((int)row('SELECT COUNT(*) AS n FROM item_photos WHERE item_id=?',[$id])['n']>0) continue;
        $created=[];db()->begin_transaction();
        try {
            lock_items([$id]);
            foreach ([['ITEM',$example['photo'],'items'],['OWNERSHIP',$example['proof'],'evidence']] as [$kind,$name,$collection]) {
                if (!$name) continue;$source=__DIR__.'/demo-photos/'.$name;
                if (!is_file($source)||filesize($source)>250000||!(($dims=getimagesize($source))&&max($dims[0],$dims[1])<=1200)) throw new RuntimeException('Licensed demo image unavailable.');
                $relative=$collection.'/'.bin2hex(random_bytes(16)).'.jpg';
                if (!copy($source,storage_dir().'/'.$relative)) throw new RuntimeException('Cannot prepare demo media.');$created[]=$relative;
                sql('INSERT INTO item_photos(item_id,photo_kind,file_path,is_primary) VALUES (?,?,?,?)',[$id,$kind,$relative,$kind==='ITEM'?1:0]);
            }
            db()->commit();
        } catch(Throwable $error){db()->rollback();cleanup_media($created);throw $error;}
    }
    echo json_encode(['database'=>app_config()['db_name'],'reports_created'=>$added,'bookmarks'=>$receipt['items'],'synthetic_classroom_data'=>true,'existing_records_preserved'=>true],JSON_PRETTY_PRINT).PHP_EOL;
}catch(Throwable $error){fwrite(STDERR,'Demo seed stopped: '.$error->getMessage().PHP_EOL);exit(1);}
