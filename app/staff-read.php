<?php
declare(strict_types=1);
require_once __DIR__ . '/workflow.php';

function staff_report(int $id,array $user): array
{
    $raw=find_item($id);
    $public=row('SELECT '.public_columns().' FROM v_items_overview v WHERE v.item_id=?',[$id]);
    $result=safe_item($public,$user);
    $result['verification_clue']=$raw['verification_clue'];
    $result['ownership_photos']=array_map(fn($p)=>app_config()['base_path'].'api/photos.php?kind=item&id='.$p['photo_id'],rows("SELECT photo_id FROM item_photos WHERE item_id=? AND photo_kind='OWNERSHIP' ORDER BY photo_id",[$id]));
    $result['allowed_statuses']=allowed_manual_statuses($raw);$result['revision']=item_revision($raw);
    $result['history']=array_map(fn($e)=>['at'=>utc_iso($e['changed_at']),'status'=>$e['status_name'],'note'=>$e['note']],rows('SELECT h.changed_at,h.note,s.status_name FROM item_status_history h JOIN item_statuses s ON s.status_id=h.status_id WHERE h.item_id=? ORDER BY h.changed_at,h.history_id',[$id]));
    return $result;
}
function staff_claim(int $id,array $user): array
{
    $raw=row('SELECT c.*,u.full_name AS claimant_name FROM claims c JOIN users u ON u.user_id=c.claimant_user_id WHERE c.claim_id=?',[$id]);
    if(!$raw)fail('Claim not found.',404,'not_found');
    $revision=claim_revision($raw);$raw['claim_id']=(string)$raw['claim_id'];
    $raw['item']=staff_report((int)$raw['item_id'],$user);
    $raw['lost']= $raw['related_lost_item_id']?staff_report((int)$raw['related_lost_item_id'],$user):null;
    $raw['ownership_photos']=array_map(fn($p)=>app_config()['base_path'].'api/photos.php?kind=claim&id='.$p['claim_photo_id'],rows('SELECT claim_photo_id FROM claim_photos WHERE claim_id=? ORDER BY claim_photo_id',[$id]));
    $raw['revision']=$revision;
    $active=in_array($raw['claim_status'],['PENDING','NEED_MORE_INFO'],true)&&in_array($raw['item']['status_code'],ACTIVE_ITEM_CODES,true)&&!$raw['item']['matched_item_id'];
    $raw['decisions']=$active?['rejected']:[];
    if($active&&(!$raw['lost']||(in_array($raw['lost']['status_code'],ACTIVE_ITEM_CODES,true)&&!$raw['lost']['matched_item_id'])))$raw['decisions'][]='approved';
    if($active&&$raw['claim_status']==='PENDING')$raw['decisions'][]='need_more_info';
    $pickup=row('SELECT handover_id FROM handovers WHERE claim_id=?',[$id]);$raw['handover_id']=$pickup?(string)$pickup['handover_id']:null;
    foreach(['created_at','updated_at','reviewed_at']as $key)$raw[$key]=utc_iso($raw[$key]);
    return $raw;
}
