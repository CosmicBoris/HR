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
    
    public function AddVacancy($vacancy){
        return $this->dbLink->insert('vacancies', $vacancy)->RunQuery();
    }
}