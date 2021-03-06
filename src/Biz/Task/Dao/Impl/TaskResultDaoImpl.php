<?php

namespace Biz\Task\Dao\Impl;

use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TaskResultDaoImpl extends GeneralDaoImpl implements TaskResultDao
{
    protected $table = 'course_task_result';

    public function analysisCompletedTaskDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) AS count, from_unixtime(finishedTime, '%Y-%m-%d') AS date FROM
            {$this->table} WHERE finishedTime >= ? AND finishedTime < ? GROUP BY date ORDER BY date ASC";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function findByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? and userId = ? ";

        return $this->db()->fetchAll($sql, array($courseId, $userId)) ?: array();
    }

    public function getByTaskIdAndUserId($taskId, $userId)
    {
        return $this->getByFields(array(
            'courseTaskId' => $taskId,
            'userId' => $userId,
        ));
    }

    public function findByTaskIdsAndUserId($taskIds, $userId)
    {
        $marks = str_repeat('?,', count($taskIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE courseTaskId IN ({$marks}) and userId = ? ;";

        $parameters = array_merge($taskIds, array($userId));

        return $this->db()->fetchAll($sql, $parameters) ?: array();
    }

    public function findByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? ";

        return $this->db()->fetchAll($sql, array($activityId, $userId)) ?: array();
    }

    public function deleteByTaskIdAndUserId($taskId, $userId)
    {
        return $this->db()->delete($this->table(), array('courseTaskId' => $taskId, 'userId' => $userId));
    }

    public function countLearnNumByTaskId($taskId)
    {
        $sql = "SELECT count(id) FROM {$this->table()} WHERE courseTaskId = ? ";

        return $this->db()->fetchColumn($sql, array($taskId));
    }

    public function findFinishedTasksByCourseIdGroupByUserId($courseId)
    {
        $sql = "SELECT count(courseTaskId) as taskCount, userId FROM {$this->table()} WHERE courseId = ? and status='finish' AND userId IN (SELECT userId FROM course_member WHERE courseId = ? AND role='student' ) group by userId";

        return $this->db()->fetchAll($sql, array($courseId, $courseId)) ?: array();
    }

    public function findFinishedTimeByCourseIdGroupByUserId($courseId)
    {
        //已发布task总数
        $sql = "SELECT count(id) FROM course_task WHERE courseId = ? AND status='published'";
        $totalTaskCount = $this->db()->fetchColumn($sql, array($courseId));

        if ($totalTaskCount <= 0) {
            return array();
        }

        $sql = "SELECT max(finishedTime) AS finishedTime, count(courseTaskId) AS taskCount, userId FROM {$this->table()}
                WHERE courseId = ? and status='finish' AND userId IN (SELECT userId FROM course_member WHERE courseId = ? AND role='student' )
                group by userId HAVING taskCount >= ?";

        return $this->db()->fetchAll($sql, array($courseId, $courseId, $totalTaskCount)) ?: array();
    }

    public function sumLearnTimeByCourseIdAndUserId($courseId, $userId)
    {
        $sql = 'SELECT sum(TIME) FROM `course_task_result` WHERE `status`= ? AND  `courseId` = ? AND `userId`= ?';

        return $this->db()->fetchColumn($sql, array('finish', $courseId, $userId));
    }

    public function getLearnedTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        $builder = $this->createQueryBuilder(array('courseTaskId' => $courseTaskId))
            ->select('sum(time) AS learnedTime')
            ->groupBy('courseTaskId');

        return $builder->execute()->fetchColumn();
    }

    public function getWatchTimeByCourseIdGroupByCourseTaskId($courseTaskId)
    {
        $builder = $this->createQueryBuilder(array('courseTaskId' => $courseTaskId))
            ->select('sum(watchTime) AS watchTime')
            ->groupBy('courseTaskId');

        return $builder->execute()->fetchColumn();
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime', 'updatedTime', 'finishedTime'),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'id IN ( :ids )',
                'status =:status',
                'userId =:userId',
                'courseId =:courseId',
                'type =: type',
                'courseTaskId =: courseTaskId',
                'courseId IN ( :courseIds )',
                'activityId =:activityId',
                'courseTaskId = :courseTaskId',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE',
                'finishedTime >= :finishedTime_GE',
                'finishedTime <= :finishedTime_LE',
                'finishedTime < :finishedTime_LT',
            ),
        );
    }
}
