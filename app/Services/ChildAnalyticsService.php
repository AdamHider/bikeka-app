<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ChildAnalyticsService
{
    protected $db;
    protected $child_id;

    public function __construct(BaseConnection $db, int $child_id)
    {
        $this->db = $db;
        $this->child_id = $child_id;
    }

    /**
     * Собирает полную аналитику
     */
    public function getFullStats(): array
    {
        $currentMonthName = date('F');
        $lastMonthName = date('F', strtotime('-1 month'));
        return [
            'main'       => $this->getMainTotals(),
            'comparison' => [
                'months' => [
                    'current' => lang("App.months." . strtolower($currentMonthName)),
                    'last'    => lang("App.months." . strtolower($lastMonthName)),
                ],
                'skills' => $this->getComparisonStats('skills_to_children', 'status', 'mastered'),
                'stages' => $this->getComparisonStats('skill_stages_to_children', 'is_completed', 1),
            ],
            'weekly' => [
                'skills' => $this->getWeeklyTrend('skills_to_children', 'status', 'mastered'),
                'stages' => $this->getWeeklyTrend('skill_stages_to_children', 'is_completed', 1),
            ],
            'domains'    => $this->getDomainAnalysis(),
        ];
    }

    private function getMainTotals(): array
    {
        $data = $this->db->table('skills_to_children as stc')
            ->select('SUM(CASE WHEN stc.status = "mastered" THEN 1 ELSE 0 END) as mastered_count,
                      SUM(CASE WHEN stc.status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count')
            ->join('skills as s', 's.id = stc.skill_id')
            ->where('stc.child_id', $this->child_id)
            ->get()->getRowArray();

        return [
            'total_mastered' => $data['mastered_count'],
            'to_learn'       => $data['in_progress_count'],
        ];
    }

    private function getComparisonStats(string $table, string $field, $value): array
    {
        $current = $this->db->table($table)
            ->where(['child_id' => $this->child_id, $field => $value])
            ->where('updated_at >=', date('Y-m-01 00:00:00'))
            ->countAllResults();

        $last = $this->db->table($table)
            ->where(['child_id' => $this->child_id, $field => $value])
            ->where('updated_at >=', date('Y-m-01 00:00:00', strtotime('-1 month')))
            ->where('updated_at <', date('Y-m-01 00:00:00'))
            ->countAllResults();

        return [
            'current' => $current,
            'last'    => $last,
            'diff'    => $current - $last,
            'trend'   => ($current >= $last) ? 'up' : 'down'
        ];
    }
    private function getWeeklyTrend(string $table, string $field, $value): array
    {
        $data = $this->db->table($table)
            ->select("WEEK(updated_at, 1) as week_num, COUNT(*) as count")
            ->where(['child_id' => $this->child_id, $field => $value])
            ->where('updated_at >=', date('Y-m-d', strtotime('-8 weeks')))
            ->groupBy('week_num')
            ->get()->getResultArray();
    
        $countsByWeek = array_column($data, 'count', 'week_num');
    
        $result = [];
        for ($i = 5; $i >= 0; $i--) {
            $timestamp = strtotime("-$i weeks");
            $wNum = (int)date('W', $timestamp); 
            
            $result[] = [
                'week_num' => $wNum,
                'label'    => date('d.m', $timestamp),
                'count'    => (int)($countsByWeek[$wNum] ?? 0)
            ];
        }
    
        return $result;
    }
    private function getDomainAnalysis(): array
    {
        $data = $this->db->table('skills_to_children as stc')
            ->select('s.domain as domain_key, 
                      SUM(CASE WHEN stc.status = "mastered" THEN 1 ELSE 0 END) as mastered_count,
                      SUM(CASE WHEN stc.status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count')
            ->join('skills as s', 's.id = stc.skill_id')
            ->where('stc.child_id', $this->child_id)
            ->groupBy('s.domain')
            ->get()->getResultArray();
    
        if (empty($data)) {
            return ['top' => [], 'weak' => []];
        }
    
        $allDomains = [];
        foreach ($data as $d) {
            $key = $d['domain_key'];
            $allDomains[] = [
                'key'            => $key,
                'title'          => lang("App.skill_domains.$key.title"),
                'icon'           => lang("App.skill_domains.$key.icon"),
                'color'          => lang("App.skill_domains.$key.color"),
                'mastered_count' => (int)$d['mastered_count'],
                'total_active'   => (int)$d['mastered_count'] + (int)$d['in_progress_count']
            ];
        }
    
        $topMastered = $allDomains;
        usort($topMastered, fn($a, $b) => $b['mastered_count'] <=> $a['mastered_count']);
    
        $weakMastered = $allDomains;
        usort($weakMastered, fn($a, $b) => $a['mastered_count'] <=> $b['mastered_count']);
    
        return [
            'top'  => array_slice($topMastered, 0, 3),
            'weak' => array_slice($weakMastered, 0, 3)
        ];
    }
}