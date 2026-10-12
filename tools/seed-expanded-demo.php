<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__.'/../app/reports.php';
require_once __DIR__.'/../app/claims.php';
require_once __DIR__.'/../app/workflow.php';
require_once __DIR__.'/demo-summary.php';
date_default_timezone_set('Asia/Jakarta');

function save_demo_receipt(string $path,array $receipt): void
{
    $temp=$path.'.pending';
    if(file_put_contents($temp,json_encode($receipt,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),LOCK_EX)===false || !rename($temp,$path)) throw new RuntimeException('Cannot persist synthetic-demo bookmarks.');
}
function attach_demo_photo(int $id,string $name,string $kind,bool $claim=false): void
{
    // Stock illustration import is CLI-only; browser uploads still use independent upload validation.
    $source=__DIR__.'/demo-photos/'.$name;
    $dims=is_file($source)?getimagesize($source):false;
    if(!$dims||max($dims[0],$dims[1])>1200||filesize($source)>250000||(new finfo(FILEINFO_MIME_TYPE))->file($source)!=='image/jpeg') throw new RuntimeException('Invalid credited demo photo.');
    $collection=$claim?'claims':($kind==='ITEM'?'items':'evidence');
    $created=[];db()->begin_transaction();
    try {
        if($claim) {
            $c=row('SELECT item_id FROM claims WHERE claim_id=?',[$id]); if(!$c)throw new RuntimeException('Claim bookmark removed.');
            lock_items([(int)$c['item_id']]);
            $existing=row('SELECT claim_photo_id FROM claim_photos WHERE claim_id=? LIMIT 1',[$id]);
        } else {lock_items([$id]);$existing=row('SELECT photo_id FROM item_photos WHERE item_id=? AND photo_kind=? LIMIT 1',[$id,$kind]);}
        if(!$existing) {
            $relative=$collection.'/'.bin2hex(random_bytes(16)).'.jpg';
            if(!copy($source,storage_dir().'/'.$relative))throw new RuntimeException('Cannot import demo illustration.');
            $created[]=$relative;
            if($claim)sql('INSERT INTO claim_photos(claim_id,file_path) VALUES (?,?)',[$id,$relative]);
            else sql('INSERT INTO item_photos(item_id,photo_kind,file_path,is_primary) VALUES (?,?,?,?)',[$id,$kind,$relative,$kind==='ITEM'?1:0]);
        }
        db()->commit();
    }catch(Throwable $e){db()->rollback();cleanup_media($created);throw $e;}
}

try {
    $path=storage_dir('setup').'/expanded-demo.json';
    $lock=fopen(storage_dir('setup').'/expanded-demo.lock','c+');
    if(!$lock||!flock($lock,LOCK_EX))throw new RuntimeException('Run setup-db.php first.');
    $basePath=storage_dir('setup').'/student-demo.json';
    if(!is_file($basePath))throw new RuntimeException('Run seed-student-demo.php first; the original examples are retained.');
    $base=json_decode(file_get_contents($basePath),true,64,JSON_THROW_ON_ERROR);
    if($base['database']!==app_config()['db_name'])throw new RuntimeException('Baseline demo receipt differs.');
    $receipt=is_file($path)?json_decode(file_get_contents($path),true,64,JSON_THROW_ON_ERROR):['version'=>1,'database'=>app_config()['db_name'],'date'=>date('Y-m-d'),'items'=>[],'claims'=>[],'steps'=>[]];
    if($receipt['version']!==1||$receipt['database']!==app_config()['db_name'])throw new RuntimeException('Expanded demo receipt differs.');
    $users=[];
    foreach(['a'=>'student-a@refind.test','b'=>'student-b@refind.test','bm'=>'bm@refind.test'] as $key=>$email){
        $u=row('SELECT user_id,role,is_active FROM users WHERE email=?',[$email]);
        if(!$u||!(int)$u['is_active']||$u['role']!==($key==='bm'?'BUILDING_MANAGEMENT':'STUDENT'))throw new RuntimeException('Expected demo identity unavailable.');
        $u['user_id']=(string)$u['user_id'];$users[$key]=$u;
    }
    $subjects=[
        ['AKG over-ear headphones',1,'headphones-overview.jpg','headphones-earcup.jpg','Gold model badge and twin-wire headband'],
        ['Brown leather wallet',3,'wallet-overview.jpg','wallet-stitching.jpg','Diagonal stitching along the inner fold'],
        ['Leather shoulder satchel',7,'satchel-overview.jpg','satchel-buckle.jpg','Brass buckle and adjustable brown strap'],
        ['Casio scientific calculator',6,'calculator-overview.jpg','calculator-overview.jpg','Model fx-991ES and dark-blue keypad'],
        ['Brown folding umbrella',8,'umbrella-overview.jpg','umbrella-overview.jpg','Tan handle and pale folded-fabric trim'],
    ];
    $templates=[];
    for($i=1;$i<=18;$i++){
        [$name,$category,$photo,$proof,$clue]=$subjects[($i-1)%count($subjects)];
        $owner=$i%2?'a':'b';$finder=$owner==='a'?'b':'a';
        foreach(['LOST'=>$owner,'FOUND'=>$finder] as $type=>$actor){
            $key=sprintf('C%02d-%s',$i,strtolower($type));
            $templates[$key]=['actor'=>$actor,'type'=>$type,'title'=>sprintf('Demo B06 · C%02d %s (%s)',$i,$name,strtolower($type)),
                'description'=>sprintf('Synthetic classroom scenario C%02d: %s. Stock illustration; simulated report for workflow demonstration.',$i,$clue),
                'category'=>$category,'location'=>35+(($i-1)%6),'date'=>(new DateTimeImmutable($receipt['date']))->modify('-'.($i*2).' days')->format('Y-m-d'),
                'time'=>$i%3===0?'':sprintf('%02d:%02d',8+($i%9),($i%4)*15),'photo'=>$photo,'proof'=>$proof,'clue'=>'Synthetic private identification: '.$clue.'. Scenario '.sprintf('C%02d',$i).'.'];
        }
    }
    $templates['C19-lost']=['actor'=>'a','type'=>'LOST','title'=>'Demo B06 · C19 spare scientific calculator (lost)',
        'description'=>'Synthetic classroom example: a spare Casio calculator last used during an examination. Stock illustration.',
        'category'=>6,'location'=>39,'date'=>(new DateTimeImmutable($receipt['date']))->modify('-80 days')->format('Y-m-d'),
        'time'=>'','photo'=>'calculator-overview.jpg','proof'=>'calculator-overview.jpg','clue'=>'Synthetic private detail: model fx-991ES; kept inside a fabric sleeve.'];
    // First seed accepts only original bookmarked examples or exact recoverable seed identities.
    if(!is_file($path)){
        $baseIds=array_map('intval',array_values($base['items']));
        foreach(rows('SELECT item_id,title,reporter_user_id,item_type FROM items') as $existing){
            if(in_array((int)$existing['item_id'],$baseIds,true))continue;
            $known=false;foreach($templates as $t)if($existing['title']===$t['title']&&$existing['item_type']===$t['type']&&(string)$existing['reporter_user_id']===$users[$t['actor']]['user_id'])$known=true;
            if(!$known)throw new RuntimeException('Initial expansion found unbookmarked reports; current user work was preserved.');
        }
    }
    if(in_array('--plan',$argv,true)){echo json_encode(['additional_reports'=>37,'additional_claims'=>12,'handovers'=>6,'synthetic'=>true,'existing_examples_retained'=>true],JSON_PRETTY_PRINT).PHP_EOL;exit;}
    save_demo_receipt($path,$receipt);$added=0;
    foreach($templates as $key=>$t){
        $u=$users[$t['actor']];
        if(isset($receipt['items'][$key])){
            $id=(int)$receipt['items'][$key];$existing=row('SELECT item_type,reporter_user_id FROM items WHERE item_id=?',[$id]);
            if(!$existing)continue; // Deliberately deleted examples are never recreated.
        }else{
            $recover=rows('SELECT item_id,item_type,reporter_user_id FROM items WHERE title=? AND reporter_user_id=?',[$t['title'],(int)$u['user_id']]);
            if(count($recover)>1)throw new RuntimeException('Ambiguous demo identity; no duplicate report was created.');
            if($recover){$existing=$recover[0];$id=(int)$existing['item_id'];}
            else{$r=mutate_report($u,['action'=>'create','type'=>$t['type'],'title'=>$t['title'],'description'=>$t['description'],'category_id'=>(string)$t['category'],'location_id'=>(string)$t['location'],'location_detail'=>'Synthetic campus scenario '.substr($key,0,3),'date'=>$t['date'],'time'=>$t['time'],'clue'=>$t['clue']]);$id=(int)$r['id'];$existing=['item_type'=>$t['type'],'reporter_user_id'=>$u['user_id']];$added++;}
            $receipt['items'][$key]=$id;save_demo_receipt($path,$receipt);
        }
        if($existing['item_type']!==$t['type']||(string)$existing['reporter_user_id']!==$u['user_id'])throw new RuntimeException('Bookmarked report identity changed.');
        if(!isset($receipt['steps']['media-'.$key])){attach_demo_photo($id,$t['photo'],'ITEM');if($t['type']==='LOST')attach_demo_photo($id,$t['proof'],'OWNERSHIP');$receipt['steps']['media-'.$key]=true;save_demo_receipt($path,$receipt);}
    }
    for($i=1;$i<=12;$i++){
        $case=sprintf('C%02d',$i);$lostId=$receipt['items'][$case.'-lost'];$foundId=$receipt['items'][$case.'-found'];
        if(!row('SELECT item_id FROM items WHERE item_id=?',[$lostId])||!row('SELECT item_id FROM items WHERE item_id=?',[$foundId]))continue;
        $owner=$users[$templates[$case.'-lost']['actor']];
        if(!isset($receipt['claims'][$case])){
            $c=row('SELECT claim_id FROM claims WHERE item_id=? AND claimant_user_id=?',[$foundId,(int)$owner['user_id']]);
            if(!$c){$r=mutate_claim($owner,['itemId'=>(string)$foundId,'lostItemId'=>(string)$lostId,'answer'=>$templates[$case.'-lost']['clue'].' Simulated ownership response.']);$c=['claim_id'=>(int)$r['id']];}
            $receipt['claims'][$case]=(int)$c['claim_id'];save_demo_receipt($path,$receipt);
        }
        $claimId=$receipt['claims'][$case];$c=row('SELECT * FROM claims WHERE claim_id=?',[$claimId]);
        if(!$c)continue;
        if((int)$c['item_id']!==$foundId||(int)$c['related_lost_item_id']!==$lostId||(string)$c['claimant_user_id']!==$owner['user_id'])throw new RuntimeException('Bookmarked claim identity changed.');
        if(!isset($receipt['steps']['proof-'.$case])){attach_demo_photo($claimId,$templates[$case.'-lost']['proof'],'OWNERSHIP',true);$receipt['steps']['proof-'.$case]=true;save_demo_receipt($path,$receipt);}
        if($i<=10&&!isset($receipt['steps']['review-'.$case])){
            $decision=$i<=6?'approved':($i<=8?'need_more_info':'rejected');$target=strtoupper($decision);
            if($c['claim_status']!==$target){if($c['claim_status']!=='PENDING')throw new RuntimeException('Review changed outside seed; current decision preserved.');review_claim($users['bm'],['action'=>'review','id'=>(string)$claimId,'decision'=>$decision,'note'=>'Synthetic B06 demonstration: '.($decision==='approved'?'identifying detail verified; prepare simulated pickup.':($decision==='need_more_info'?'please clarify the model detail and supporting illustration.':'provided ownership details do not match this mock scenario.')),'revision'=>claim_revision($c),'confirm'=>'1']);}
            $receipt['steps']['review-'.$case]=true;save_demo_receipt($path,$receipt);
        }
        if($i<=6){
            $h=row('SELECT * FROM handovers WHERE claim_id=?',[$claimId]);if(!$h)throw new RuntimeException('Approved demo has no pickup.');
            $ops=match($i){1=>[],2=>['schedule','cancel_schedule'],3=>['schedule'],4=>['schedule','reschedule'],5=>['complete'],6=>['schedule','complete']};
            foreach($ops as $index=>$op){
                $step='pickup-'.$case.'-'.$index;if(isset($receipt['steps'][$step]))continue;
                $h=row('SELECT * FROM handovers WHERE handover_id=?',[(int)$h['handover_id']]);
                $data=['id'=>(string)$h['handover_id'],'action'=>$op,'revision'=>handover_revision($h),'confirm'=>'1','note'=>'Synthetic B06 demonstration: '.$op.' simulated classroom pickup.'];
                if(in_array($op,['schedule','reschedule'],true)){$data['pickup_location_id']=(string)(35+(($i+$index)%6));$data['scheduled_at']=(new DateTimeImmutable('+'.(2+$i+$index).' days'))->setTime(14,30)->format('Y-m-d\TH:i');}
                // A crash after commit is recoverable by the seed-specific private snapshot note.
                if($h['notes']!==$data['note'])mutate_handover($users['bm'],$data);
                $receipt['steps'][$step]=true;save_demo_receipt($path,$receipt);
            }
        }
    }
    foreach(['C13-lost'=>'UNDER_REVIEW','C13-found'=>'UNDER_REVIEW','C14-found'=>'UNCLAIMED_CLOSED','C15-lost'=>'UNCLAIMED_CLOSED','C15-found'=>'UNCLAIMED_CLOSED','C16-lost'=>'RESOLVED','C17-lost'=>'OPEN','C17-found'=>'OPEN'] as $key=>$status){
        $step='status-'.$key;if(isset($receipt['steps'][$step]))continue;$id=$receipt['items'][$key];
        if(!row('SELECT item_id FROM items WHERE item_id=?',[$id]))continue;
        $item=find_item($id);if($item['status_code']!==$status)staff_status($users['bm'],['action'=>'status','id'=>(string)$id,'status'=>$status,'note'=>'Synthetic B06 demonstration: operational '.$status.' scenario.','revision'=>item_revision($item),'confirm'=>'1']);
        $receipt['steps'][$step]=true;save_demo_receipt($path,$receipt);
    }
    $receipt['complete']=true;save_demo_receipt($path,$receipt);flock($lock,LOCK_UN);fclose($lock);
    echo json_encode(['scope'=>'Synthetic classroom mock scenarios through real business workflow; not real campus returns','reports_created'=>$added,'bookmarks'=>$receipt['items'],'claim_bookmarks'=>$receipt['claims'],'summary'=>demo_summary(),'existing_records_preserved'=>true],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'Expanded demo seed stopped: '.$e->getMessage().PHP_EOL);exit(1);}
