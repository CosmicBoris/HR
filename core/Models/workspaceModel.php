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

    function AddCandidate($candidate, $vacancy_id)
    {
        $this->dbLink->autocommit(FALSE);

        $result = $this->dbLink->insert('candidates', $candidate)->RunQuery();
        $candidate_id = $this->dbLink->lastInsertedId();
        if($result)
        {
            $result = $this->dbLink->insert('user_candidates',
                ['user_id'=>Auth::GetUserID(), 'candidate_id'=>$candidate_id])->RunQuery();
        }
        if($vacancy_id && $result)
            $result = $this->AssingVacancy($vacancy_id, $candidate_id);

        $this->dbLink->commit();
        return $result;
    }
    function UpdateCandidate(Candidate $c)
    {
        return $this->dbLink->update('candidates',
            [
                "fullname"=>$c->fullname,
                "sex"=>$c->sex,
                "age"=>$c->age,
                "profile"=>$c->profile,
                "email"=>$c->email,
                "phone"=>$c->phone,
                "skills"=>$c->skills
            ])
            ->where(['id' => $c->id])->RunQuery();
    }

    function CandidatesCount()
    {
        return $this->dbLink->select('candidates', 'id')
            ->innerJoin('user_candidates', ['id'=>'candidate_id'])
            ->where(['user_id'=>Auth::GetUserID()])
            ->RunQuery()->num_rows;
    }
    function getCandidate($id)
    {
        return $this->dbLink->GetCandidate($id);
    }
    function getCandidates($from = 0)
    {
        $cs = $this->dbLink->GetCandidates(paginationHelper::Limit());
        if( !$cs && $from > 0 ){
            paginationHelper::setCurrentPage(paginationHelper::getCurrentPage() - 1);
            $cs = $this->dbLink->GetCandidates(paginationHelper::Limit());
        }
        return $cs;
    }
    function FindCandidate(Candidate $c)
    {
        $sql = 'SELECT '.$this->dbLink->GetSafeStr(array_keys(get_object_vars($c))).' FROM `candidates` '
            .'LEFT JOIN `user_candidates` AS `uc` ON `candidates`.`id`=`uc`.`candidate_id` '
            .'WHERE `uc`.`user_id`='.Auth::GetUserID().' AND ';
        $sql2 ="";
        if(!empty($c->email))
            $sql2 = '`candidates`.`email`='.$this->dbLink->GetSafeStr($c->email, true).' ';
        if(!empty($c->profile)){
            if(!empty($sql2))
                $sql2 .= 'OR ';
            $sql2 .= '`candidates`.`profile`='.$this->dbLink->GetSafeStr($c->profile, true).' ';
        }
        if(!empty($c->phone)){
            if(!empty($sql2))
                $sql2 .= 'OR ';
            $sql2 .= '`candidates`.`phone`='.$this->dbLink->GetSafeStr($c->phone, true).' ';
        }
        if(!empty($c->fullname)){
            if(!empty($sql2))
                $sql2 .= 'OR ';
            $sql2 .= '`candidates`.`fullname`='.$this->dbLink->GetSafeStr($c->fullname, true).' ';
        }
        $result = $this->dbLink->ExecuteSql($sql.$sql2);

        if($result) {
            $candidate = new Candidate($result);
            $warnings = array();

            if($candidate->fullname == $c->fullname)
                $warnings[] = "Name in Base";
            if($candidate->phone == $c->phone)
                $warnings[] = "Phone in Base";
            if($candidate->profile == $c->profile)
                $warnings[] = "Profile in Base";
            if($candidate->email == $c->email)
                $warnings[] = "Email in Base";

            return implode(', ', $warnings);
        }
        return null;
    }
    function AssingVacancy($vacancy_id, $candidate_id)
    {
        return $this->dbLink->insert('vacancies_candidates',
            ['vacancy_id'=>$vacancy_id, 'candidate_id'=>$candidate_id])->RunQuery();
    }

    function AddVacancy($vacancy, $candidate_id)
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
    function VacanciesCount(){
        return $this->dbLink->select('vacancies', 'id')
            ->where(['user_id'=>Auth::GetUserID()])
            ->RunQuery()->num_rows;
    }
    function PrepareRow($data, &$rows)
    {
        $n = paginationHelper::Limit();
        if(is_array($data) && is_array($data[0])) {
            foreach ($data as $obj){
                $v = new Vacancy($obj);
                $v->N = (int)++$n;
                $v->assigned = $obj['assigned'];
                $rows[] = $v;
            }
        } else if (is_array($data)){
            $v = new Vacancy($data);
            $v->N = (int)++$n;
            $v->assigned = $data['assigned'];
            $rows[] = $v;
        }
    }
    function getVacancies($from = 0, $searchStr = false)
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
        if(!$result) return $vacancies;

        $this->PrepareRow($result, $vacancies);
        
        if(count($vacancies) == 0 && $from > 0) {
            paginationHelper::setCurrentPage(paginationHelper::getCurrentPage() - 1);
            $limit = "LIMIT ".paginationHelper::Limit().",".paginationHelper::$elementsPerPage;

            $result = $this->dbLink->ExecuteSql(($from != self::GET_ALL ) ? $sql.$order.$limit : $sql.$order);
            if(!$result) return false;

            $this->PrepareRow($result, $vacancies);
        }
        return $vacancies;
    }
    function GetAssignedVacancies(int $candidateId)
    {
        $result = $this->dbLink->select('vacancies_candidates',
            ['id', 'user_id', 'title', 'state', 'date_added', 'description'])
            ->innerJoin('vacancies', ['vacancy_id'=>'id'])
            ->where(['user_id'=>Auth::GetUserID(), "state" => "1"], "=", "AND")
            ->RunQuery();

        $vacancies = [];
        if ($result !== false) {
            while ($obj = $result->fetch_assoc()) {
                $vac = new Vacancy($obj);
                $vacancies[]= $vac;
            }
        }
        return $vacancies;
    }

    function FindVacancy(Vacancy $v)
    {
        $result = $this->dbLink->select('vacancies', array_keys(get_object_vars($v)))
            ->where(['title'=>$v->title])
            ->RunQuery();
        if($result->num_rows > 0){
            return 'Vacancy with such title exists!';
        }
        return null;
    }
    
    function AddEvent($event)
    {
         return $this->dbLink->insert('events', $event)->RunQuery();
    }
    function getEvents($start, $end, array &$out)
    {
        $event = new Event();
        $result = $this->dbLink->select('events', array_keys(get_object_vars($event)))
            ->where(['start'=>$start, $end], 'BETWEEN','AND')
            ->RunQuery();

        if(!$result) return 0;

        while($obj = $result->fetch_assoc()){
            $event = new Event($obj);
            /*$event->start = strtotime($obj['start']) . '000';
            $event->end   = strtotime($obj['end']) .'000';*/
            $out[] = $event;
        }

        return 1;
    }
    
    function GenerateCandidatesTableContent($page, &$response)
    {
        $candidates = $this->getCandidates($page);

        foreach( $candidates as $c ){
            $c->btnInfo = htmlbuttonHelper::Form(
                ["id" =>$c->id, "class" => "btn btn-sm btn_c", "data-action" => "info",
                    '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>']
            );
            $c->btnRemove = htmlbuttonHelper::Form(
                ["id" =>$c->id, "class" => "btn btn-sm btn_c", "data-action" => "delete",
                    '<span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>']
            );
        }

        $ht = new htmltableHelper();
        $response['table'] = $ht->BodyFromObj($candidates,
            ['N', 'fullname', 'email', 'phone','assigned','btnInfo','btnRemove']
        )->getTableBody();

        $response['vCount'] = $this->CandidatesCount();
        $response['pagination'] = paginationHelper::Form(
            $response['vCount'], "/workspace/Candidates"
        );
    }
    function GenerateVacanciesTableContent($page, &$response)
    {
        $vacancies = $this->getVacancies($page);

        foreach( $vacancies as $vac )
        {
            $vac->state = $vac->state ? "Open" : "Closed";
            $vac->btnInfo = htmlbuttonHelper::Form(
                ["id" =>$vac->id, "class" => "btn btn-sm btn_c", "data-action" => "info",
                    '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>']
            );
            $vac->btnRemove = htmlbuttonHelper::Form(
                ["id" =>$vac->id, "class" => "btn btn-sm btn_c", "data-action" => "delete",
                    '<span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>']
            );
            unset($vac->id);
            unset($vac->user_id);
        }

        $ht = new htmltableHelper();
        $response['table'] = $ht->BodyFromObj($vacancies,
            ['N','title', 'description', 'date_added', 'assigned','state','btnInfo','btnRemove']
        )->getTableBody();

        $response['vCount'] = $this->VacanciesCount();
        $response['pagination'] = paginationHelper::Form(
            $response['vCount'], "/workspace/Vacancies"
        );
    }

    function Delete($from, $id)
    {
        $result = [];

        if($this->dbLink->delete($from)->where(['id'=>$id])->RunQuery()){
            $result['success'] = 1;
        } else {
            $result['error'] = $this->dbLink->getErrors();
        }
        return $result;
    }
}