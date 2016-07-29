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
        paginationHelper::setCurrentPage($_GET['page'] ?? 1);
    }
    public function actionIndex()
    {
        $this->_view->fullRender();
    }
    
    public function actionEvents()
    {
        if($this->isPost()) {
            $response = array();
            $validator = Validator::GetInstance();
            $validator->Prepare($_POST)->CheckForEmpty();
            if(!$validator->IsError()) {
                $event = new Event($_POST);
                $event->class = $event->event_type;
                $event->className = $event->event_type;
                $event->start = date("Y-m-d H:i:s", strtotime($event->start));
                $event->end = date("Y-m-d H:i:s", strtotime($event->end));
                $response['success'] = $this->_model->AddEvent($event);
            } else {
                $response['warning'] = "Empty fields: " . implode(', ', $validator->GetErrors()['empty']);
            }
            Response::ReturnJson($response);
        } else {
            $this->_view->vacancies = $this->_model->getVacancies(-1);
            $this->_view->users = $this->_model->getCandidates();
            $this->_view->SetTitle('Events');
            $this->_view->render();
        }
    }
    public function actionFeed()
    {
        $events = array();
        $start = $_REQUEST['start'];
        $end   = $_REQUEST['end'];
        $this->_model->getEvents($start, $end, $events);

        Response::ReturnJson($events);
    }
    public function actionCalFeed()
    {
        $start = $_REQUEST['from'] / 1000;
        $end   = $_REQUEST['to']   / 1000;
        
    }

    public function actionCandidateInfo()
    {
        $this->_view->candidate = $this->_model->getCandidate($_GET['id']);
        $this->_view->assignedVacancies = $this->_model->GetAssignedVacancies($_GET['id']);

        $this->_view->render();
    }
    public function actionCandidates()
    {
        $this->_view->candidates = $this->_model->getCandidates(paginationHelper::getCurrentPage());
        $this->_view->vCount = $this->_model->CandidatesCount();
        $this->_view->vacancies = $this->_model->getVacancies(-1);

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
                            $response['pagination'] = paginationHelper::Form($response['vCount'], "/workspace/Candidates");
                        } else {
                            $response['error'] = $this->_model->getDBError();
                        }
                    }
                } else {
                    $response['warning'] = "E-mail not correct!";
                }    
            } else
                $response['warning'] = "Empty fields: " . implode(', ', $validator->GetErrors()['empty']);

            Response::ReturnJson($response);
        }
    }
    public function actionEditCandidate()
    {
        if($this->_model->UpdateCandidate(new Candidate($_POST)))
            Response::ReturnJson(['success'=>1]);
    }
    public function actionVacancyInfo()
    {
        $this->_view->users = $this->_model->getCandidates(-1);
        $this->_view->vCount = $this->_model->VacanciesCount();
        $this->_view->vacancies = $this->_model->getVacancies(paginationHelper::getCurrentPage());

        $this->_view->render();
    }
    public function actionVacancies()
    {
        $this->_view->users = $this->_model->getCandidates(-1);
        $this->_view->vCount = $this->_model->VacanciesCount();
        $this->_view->vacancies = $this->_model->getVacancies(paginationHelper::getCurrentPage());

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

                    } else
                        $response['error'] = $this->_model->getDBError();
                }
            } else
                $response['warning'] = 'Empty fields!';

            Response::ReturnJson($response);
        }
    }

    public function actionJournal()
    {

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