<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 30.12.2015
 * Time: 16:56
 */
class WorkspaceController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if(Auth::isAdmin()) {
            header("Location: /super");
            exit();
        }
        else if(!Auth::IsLogged()){
            header("Location: /login");
            die();
        }
        $this->_model = new workspaceModel();
        $this->_view->SetTitle('My workspace');
    }
    public function actionIndex()
    {
        $this->_view->render();
    }
    
    public function actionEvents()
    {
        if($this->isPost()) {
            $response = array();
            $event = new Event($_POST);
            $event->start = date("Y-m-d H:i:s", strtotime($event->start));
            $event->end = date("Y-m-d H:i:s", strtotime($event->end));
            $response['success'] = $this->_model->AddEvent($event);
            
            Response::ReturnJson($response);
        } else {
            $this->_view->page = $_GET['page'];
            $this->_view->vacancies = $this->_model->getVacancies(-1);
            $this->_view->users = $this->_model->getCandidates();

            $this->_view->SetTitle('Events');
            if($this->isAjax())
                $this->_view->partialRender();
            else {
                $this->_view->render();
            }
        }
    }
    public function actionFeed()
    {
        $start = date('Y-m-d h:i:s', ($_GET['start'] / 1000));
        $end = microtime();
        echo '{"success": 1,"result": [{"id": 293,"title": "Event 1","url": "http://example.com","class": "event-important","start": '.$end.'"end":'. $end.'}]}';
    }

    public function actionCandidates()
    {
        paginationHelper::setCurrentPage((int)$_GET['page']);
        $this->_view->candidates = $this->_model->getCandidates(paginationHelper::Limit());
        $this->_view->vCount = $this->_model->CandidatesCount();
        $this->_view->vacancies = $this->_model->getVacancies(-1);

        $this->_view->SetTitle('Candidates');
        if($this->isAjax())
            $this->_view->partialRender();
        else
            $this->_view->render();
    }
    public function actionAddCandidate()
    {
        if($this->isPost()) {
            $response = array();
            $validator = Validator::GetInstance();
            $validator->Prepare($_POST);
            if($validator->CheckEmail('email'))
            {
                $candidate = new Candidate($validator->GetAllFields());

                if(!$response['warning'] = $this->_model->FindCandidate($candidate))
                {
                    if($this->_model->AddCandidate($candidate, $_POST['vacancy_id'])) {
                        $response['success'] = 1;

                        $candidates = $this->_model->getCandidates(0);

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

                        $response['vCount'] = $this->_model->CandidatesCount();
                        $response['pagination'] = paginationHelper::Form(
                            $response['vCount'], "/workspace/Candidates"
                        );
                    } else {
                        $response['error'] = $this->_model->getDBError();
                    }
                }
            } else {
                $response['warning'] = "E-mail not correct!";
            }

            Response::ReturnJson($response);
        }
    }

    public function actionVacancies()
    {
        paginationHelper::setCurrentPage((int)$_GET['page']);
        $this->_view->users = $this->_model->getCandidates(-1);
        $this->_view->vCount = $this->_model->VacanciesCount();
        $this->_view->vacancies = $this->_model->getVacancies($this->_view->page);
            
        $this->_view->SetTitle('Vacancies');
        if($this->isAjax())
            $this->_view->partialRender();
        else
            $this->_view->render();
    }
    public function actionAddVacancy()
    {
        if($this->isPost()) {
            $response = array();

            $validator = Validator::GetInstance();
            $validator->Prepare($_POST, ['title','description'])->CheckForEmpty();

            if(!$validator->IsError()) {
                $vacancy = new Vacancy($validator->GetAllFields());
                $vacancy->user_id = Auth::GetUserID();

                if(!$response['warning'] = $this->_model->FindVacancy($vacancy)) {
                    if($this->_model->AddVacancy($vacancy, $_POST['candidate_id'])) {
                        $response['success'] = 1;

                        $vacancies = $this->_model->getVacancies(0);

                        foreach( $vacancies as $vac )
                        {
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
                            unset($vac->state);
                        }

                        $ht = new htmltableHelper();
                        $response['table'] = $ht->BodyFromObj($vacancies,
                            ['title', 'description', 'date_added', 'assigned','btnInfo','btnRemove']
                        )->getTableBody();

                        $response['vCount'] = $this->_model->VacanciesCount();
                        $response['pagination'] = paginationHelper::Form(
                            $response['vCount'], "/workspace/Vacancies"
                        );

                    } else {
                        $response['error'] = $this->_model->getDBError();
                    }
                }

            } else {
                $response['warning'] = 'Empty fields!';
            }
            Response::ReturnJson($response);
        }
    }

    public function actionJournal()
    {
        /*if($csearch = $_GET['search']) {
            Response::ReturnJson();
            $resp = array('heading' => 'Результати пошуку: '.$csearch);
            $entitys = $this->_model->GetMyEntitys(Url::GetParam(), $csearch);

            foreach( $entitys as &$entity ) {
                $entity[] = htmlbuttonHelper::Form(
                    array("class"=>"btn btn-info btn-xs",
                        "id"=>$entity['Entity_EDRPOU'],
                        '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>')
                );
                $entity[] = htmlbuttonHelper::Form(
                    array("class"=>"btn btn-warning btn-xs",
                        "id"=>$entity['Entity_EDRPOU'],
                        'ред.<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>')
                );
            }
            $ht = new htmltableHelper();
            $resp['table'] = $ht->Body($entitys)->getTbody();
            $resp['pagination'] = paginationHelper::Form(
                $this->_model->GetMYEntitysCount($csearch), "workspace/LoadMyEntitys");
            echo json_encode($resp);
        } else {
            $this->_view->heading = 'Мої підприємсва:';
            $this->_view->numRows = $this->_model->GetMYEntitysCount();
            $this->_view->myco = $this->_model->GetMyEntitys(Url::GetParam());
            $this->_view->partialRender('entityTable');
        }*/
    }

    public function actionDelete()
    {
        Response::ReturnJson($this->_model->Delete($_GET['table'], $_GET['id']));
    }
}