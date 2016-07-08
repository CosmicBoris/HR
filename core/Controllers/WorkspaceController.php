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
        if(Auth::IsAdmin()) {
            header("Location: /super");
            exit();
        } else if(!Auth::IsLogged()){
            header("Location: /login");
            die();
        }
        $this->_model = new workspaceModel();
        paginationHelper::setCurrentPage((int)$_GET['page']);
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
            $event->class = $event->event_type;
            $event->className = $event->event_type;
            $event->start = date("Y-m-d H:i:s", strtotime($event->start));
            $event->end = date("Y-m-d H:i:s", strtotime($event->end));
            $response['success'] = $this->_model->AddEvent($event);
            
            Response::ReturnJson($response);
        } else {
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
        $events = array();
        //$events['result'] = array();
        if(isset($_GET['from'])){
            $start = $_REQUEST['from'] / 1000;
            $end   = $_REQUEST['to']   / 1000;
            //$events['success'] = $this->_model->getEvents(date('Y-m-d', $start), date('Y-m-d', $end), $events['result']);
        } else {
            $start = $_REQUEST['start'];
            $end   = $_REQUEST['end'];
            $this->_model->getEvents($start, $end, $events);
        }

        Response::ReturnJson($events);
    }

    public function actionCandidates()
    {

        $this->_view->candidates = $this->_model->getCandidates(paginationHelper::getCurrentPage());
        $this->_view->vCount = $this->_model->CandidatesCount();
        $this->_view->vacancies = $this->_model->getVacancies(-1);

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
            $validator->Prepare($_POST, ["fullname", "phone", "age", "skills", "sex"])->CheckForEmpty();
            if(!$validator->IsError()){
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
            } else {
                $response['warning'] = "Empty fields: " . implode(', ', $validator->GetErrors()['empty']);
            }

            Response::ReturnJson($response);
        }
    }

    public function actionVacancies()
    {
        $this->_view->users = $this->_model->getCandidates(-1);
        $this->_view->vCount = $this->_model->VacanciesCount();
        $this->_view->vacancies = $this->_model->getVacancies(paginationHelper::getCurrentPage());

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
                            ['N','title','description','date_added','assigned','btnInfo','btnRemove']
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

    public function actionDeleteCandidate()
    {
        $response = $this->_model->Delete('candidates', $_GET['id']);
        if($response['success'] == 1){
            $this->_model->GenerateCandidatesTableContent(paginationHelper::getCurrentPage(), $response);
        }

        Response::ReturnJson($response);
    }
    public function actionDeleteVacancy()
    {
        $response = $this->_model->Delete('vacancies', $_GET['id']);
        if($response['success'] == 1){
            $this->_model->GenerateVacanciesTableContent(paginationHelper::getCurrentPage(), $response);
        }

        Response::ReturnJson($response);
    }
}