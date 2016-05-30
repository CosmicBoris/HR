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
    public function CandidatesCount()
    {
        return $this->dbLink->select('candidates', 'id')
            ->innerJoin('user_candidates', ['id'=>'candidate_id'])
            ->where(['user_id'=>Auth::GetUserID()])
            ->RunQuery()->num_rows;
    }
    public function getCandidate($id)
    {
        return $this->dbLink->GetCandidate($id);
    }
    public function getCandidates($from = 0)
    {
        $cs = $this->dbLink->GetCandidates($from);
        if( !$cs && $from > 0 ){
            paginationHelper::setCurrentPage(paginationHelper::getCurrentPage() - 1);
            $cs = $this->dbLink->GetCandidates(paginationHelper::Limit());
        }

        return $cs;
    }
    public function FindCandidate(Candidate $c)
    {
        $candidate = new Candidate($this->dbLink->FindCandidate($c));
        if($candidate) {
            $par = array();

            if($candidate->fullname == $c->fullname)
                $par[] = "Name in Base";
            if($candidate->phone == $c->phone)
                $par[] = "Phone in Base";
            if($candidate->profile == $c->profile)
                $par[] = "Profile in Base";
            if($candidate->email == $c->email)
                $par[] = "Email in Base";

            return implode(', ', $par);
        }
        return 0;
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

        $vacancies = array();

        if($from != self::GET_ALL) $order .= "LIMIT ".paginationHelper::Limit().",".paginationHelper::$elementsPerPage;

        $result = $this->dbLink->ExecuteSql($sql.$order);
        if(!$result) return false;

        $n = paginationHelper::Limit();
        foreach ($result as $obj){
            $v = new Vacancy($obj);
            $v->N = (int)++$n;
            $v->assigned = $obj['assigned'];
            $vacancies[] = $v;
        }
        
        if(count($vacancies) == 0 && $from > 0) {
            paginationHelper::setCurrentPage(paginationHelper::getCurrentPage() - 1);
            $limit = "LIMIT ".paginationHelper::Limit().",".paginationHelper::$elementsPerPage;

            $result = $this->dbLink->ExecuteSql(($from != self::GET_ALL ) ? $sql.$order.$limit : $sql.$order);
            if(!$result) return false;

            $n = paginationHelper::Limit();
            foreach ($result as $obj){
                $v = new Vacancy($obj);
                $v->N = (int)++$from;
                $v->assigned = $obj['assigned'];
                $vacancies[] = $v;
            }
        }
        return $vacancies;
    }
    public function FindVacancy(Vacancy $v)
    {
        $result = $this->dbLink->select('vacancies', array_keys(get_object_vars($v)))
            ->where(['title'=>$v->title])
            ->RunQuery();
        if($result->num_rows > 0){
            return 'Vacancy with such title exists!';
        }
        return 0;
    }
    
    public function AddEvent($event)
    {
         return $this->dbLink->insert('events', $event)->RunQuery();
    }

    public function Delete($from, $id)
    {
        $result = array();

        if($this->dbLink->delete($from)->where(['id'=>$id])->RunQuery()){
            $result['success'] = 1;
        } else {
            $result['success'] = 0;
            $result['error'] = $this->dbLink->getErrors();
        }

        return $result;
    }
}