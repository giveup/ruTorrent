<?php

require_once( 'rules.php' );

class rssurlrewriteHooks
{
    public static function OnRSSFetched($prm)
    {
        $mngr = new rRSSManager();
        $rules = rURLRewriteRulesList::load($mngr);
        $newHrefs = [];
        foreach ($prm["rss"]->items as $href => &$item) {
            $oldHref = $href;
            $rules->apply($prm["rss"], $mngr->groups, $href, $item['guid']);
            if ($oldHref != $href) {
                $newHrefs[$href] = $item;
                unset($prm["rss"]->items[$oldHref]);
            }
        }
        if (count($newHrefs)) {
            $prm["rss"]->items = array_merge($prm["rss"]->items, $newHrefs);
        }
    }
}
