<?php
/**
 * @package Newscoop
 * @subpackage Soundcloud plugin
 * @copyright 2011 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

class Soundcloud extends DatabaseObject
{
    public $m_keyColumnNames = array('article_id', 'track_id');
    public $m_keyIsAutoIncrement = true;
    public $m_dbTableName = 'plugin_soundcloud';
    public $m_columnNames = array(
        'article_id',
        'track_id',
        'track_data',
        );

    public function Soundcloud($p_article_id = null, $p_track_id = null)
    {
        parent::DatabaseObject($this->m_columnNames);

        $this->m_data['article_id'] = $p_article_id;
        $this->m_data['track_id'] = $p_track_id;

        if ($this->keyValuesExist()) {
            $this->fetch();
        }
    }

    public function create($p_article_id = null, $p_track_id = null, $p_track_data = null)
    {
        if (empty($p_article_id) || empty($p_track_id) || empty($p_track_data)) {
            return false;
        }
        $values = array(
            'article_id' => $p_article_id,
            'track_id' => $p_track_id,
            'track_data' => serialize($p_track_data),
        );
        $success = parent::create($values);
        if (!$success) {
            return false;
        }

        $CampCache = CampCache::singleton();
        $CampCache->clear('user');

        return true;
    }

    public static function getAssignments($p_article_id, $p_order = 'asc', $p_start = 0, $p_limit = 0)
    {
        global $g_ado_db;
        $tracks = array();
        $query = 'SELECT    track_data
                  FROM      plugin_soundcloud
                  WHERE     article_id = ' . $g_ado_db->escape($p_article_id) .
                ' ORDER BY track_id ' . $p_order;
        if ($p_limit) {
            $query .= " LIMIT $p_start, $p_limit";
        }
        if ($res = $g_ado_db->getAll($query)) {
            foreach ($res as $track) {
                $tracks[] = @unserialize($track['track_data']);
            }
        }

        return $tracks;
    }

    public static function deleteAllTracks($p_track_id)
    {
        global $g_ado_db;
        $query = 'DELETE
                  FROM      plugin_soundcloud
                  WHERE     track_id = ' . $g_ado_db->escape($p_track_id);
        $ret = $g_ado_db->execute($query);

        $CampCache = CampCache::singleton();
        $CampCache->clear('user');

        return $res;
    }

    public function delete()
    {
        $deleted = parent::delete();
        $CampCache = CampCache::singleton();
        $CampCache->clear('user');

        return $deleted;
    }
}
