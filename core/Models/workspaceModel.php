<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 25.01.2016
 * Time: 15:08
 */
class WorkspaceModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function GetMyEntitys($from, $param = false)
    {
        $entitys = $this->dbLink->GetEntitysInfo(paginationHelper::Limit($from), $param);
        if(count($entitys)==0 && $from > 0){
            paginationHelper::setCurrentPage($from-1);
            return $this->dbLink->GetEntitysInfo(paginationHelper::Limit($from-1), $param);
        }else{
            return $entitys;
        }
    }
    
    public function GetMYEntitysCount($param = false)
    {
        if(isset($param)){
            return $this->dbLink->select(array('entity' => 'e'),
                array('e' => array('Entity_Id')))
                ->innerJoin(array('entity_user'=>'eu'),array('Entity_Id' => 'EntityId'))
                ->where(array('eu.UserId'=>Auth::GetUserID(), 'e.Entity_EDRPOU' => '%'.$param.'%'),'LIKE','AND')
                ->RunQuery()->num_rows;
        }
        return $this->dbLink->select('entity_user', 'EntityId')
            ->where(array('UserId'=>Auth::GetUserID()))
            ->RunQuery()->num_rows;
    }
    
    public function GetCandidates($id = false) {
        if($id) return $this->dbLink->GetCandidate($id);
        return $this->dbLink->GetCandidates();
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
    public function vacanciesCount(){
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

        $result = $this->dbLink->ExecuteSql($sql.$order.$limit);
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

            $result = $this->dbLink->ExecuteSql($sql.$order.$limit);
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