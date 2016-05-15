<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 25.01.2016
 * Time: 15:08
 */
class WorkspaceModel extends Model
{
    const GET_ALL = -1;

    public function __construct()
    {
        parent::__construct();
    }

    public function AddCandidate($candidate, $vacancy_id)
    {
        $this->dbLink->autocommit(FALSE);

        $result = $this->dbLink->insert('candidates', $candidate)->RunQuery();
        $candidate_id = $this->dbLink->lastInsertedId();
        if($result) {
            $result = $this->dbLink->insert('user_candidates',
                ['user_id'=>Auth::GetUserID(), 'candidate_id'=>$candidate_id])->RunQuery();
        }
        if($vacancy_id && $result) {
            $result = $this->dbLink->insert('vacancies_candidates',
                ['vacancy_id'=>$vacancy_id, 'candidate_id'=>$candidate_id])->RunQuery();
        }

        $this->dbLink->commit();
        return $result;
    }
    public function CandidatesCount(){
        return $this->dbLink->select('candidates', 'id')
            ->innerJoin('user_candidates', ['id'=>'candidate_id'])
            ->where(['user_id'=>Auth::GetUserID()])
            ->RunQuery()->num_rows;
    }
    public function getCandidate($id) {
        return $this->dbLink->GetCandidate($id);
    }
    public function getCandidates($from = -1)
    {
        $cs = $this->dbLink->GetCandidates($from);
        if( !$cs && $from > 0 )
            $cs = $this->dbLink->GetCandidates($from-1);
        return $cs;
    }


    public function AddVacancy($vacancy, $candidate_id)
    {
        $this->dbLink->autocommit(FALSE);

        $result = $this->dbLink->insert('vacancies', $vacancy)->RunQuery();

        if($candidate_id && $result) {
            $vac_id = $this->dbLink->lastInsertedId();
            $result = $this->dbLink->insert('vacancies_candidates',
                ['vacancy_id'=>$vac_id, 'candidate_id'=>$candidate_id])->RunQuery();
        }

        $this->dbLink->commit();
        return $result;
    }
    public function VacanciesCount(){
        return $this->dbLink->select('vacancies', 'id')
            ->where(['user_id'=>Auth::GetUserID()])
            ->RunQuery()->num_rows;
    }
    public function getVacancies($from = 0, $searchStr = false)
    {
        $sql = "SELECT `id`,`user_id`,`title`,`state`,`date_added`,`description`, "
            ."COUNT(`vc`.`vacancy_id`) AS `assigned` "
            ."FROM `vacancies` "
            ."LEFT JOIN `vacancies_candidates` AS `vc` ON `vacancies`.`id`=`vc`.`vacancy_id` "
            ."WHERE `user_id`=".Auth::GetUserID()
            ." GROUP BY `id` ";
        $order = 'ORDER BY `date_added` DESC ';
        $limit ="LIMIT ".paginationHelper::Limit($from).",".paginationHelper::$elementsPerPage;

        $vacancies = array();

        $result = $this->dbLink->ExecuteSql( ($from != self::GET_ALL ) ? $sql.$order.$limit : $sql.$order);
        if(!$result) return false;

        $n = paginationHelper::Limit($from);
        foreach ($result as $obj){
            $v = new Vacancy($obj);
            $v->N = (int)++$n;
            $v->assigned = $obj['assigned'];
            $vacancies[] = $v;
        }
        
        if(count($vacancies) == 0 && $from > 0) {
            paginationHelper::setCurrentPage($from-1);

            $limit = "LIMIT ".paginationHelper::Limit($from-1).",".paginationHelper::$elementsPerPage;

            $result = $this->dbLink->ExecuteSql(($from != self::GET_ALL ) ? $sql.$order.$limit : $sql.$order);
            if(!$result) return false;

            $n = paginationHelper::Limit($from-1);
            foreach ($result as $obj){
                $v = new Vacancy($obj);
                $v->N = (int)++$from;
                $v->assigned = $obj['assigned'];
                $vacancies[] = $v;
            }
        }
        return $vacancies;
        /*$vacancies = array();
        $v = new Vacancy();
        $result = $this->dbLink->select('vacancies', array_keys(get_object_vars($v)))
            ->where(['user_id'=>Auth::GetUserID()])
            ->limit(paginationHelper::Limit($from), paginationHelper::$elementsPerPage)
            ->RunQuery();
        if(!$result) return false;

        while ($obj = $result-> fetch_assoc())
        {
            $v = new Vacancy($obj);
            $vacancies[] = $v;
        }
        $result->close();

        if(count($vacancies) == 0 && $from > 0) {
            paginationHelper::setCurrentPage($from-1);

            $result = $this->dbLink->select('vacancies', array_keys(get_object_vars($v)))
                ->where(['user_id'=>Auth::GetUserID()])
                ->limit(paginationHelper::Limit($from-1), paginationHelper::$elementsPerPage)
                ->RunQuery();
            while ($obj = $result-> fetch_assoc())
            {
                $v = new Vacancy($obj);
                $vacancies[] = $v;
            }
            $result->close();
        }
        return $vacancies;*/
    }
}