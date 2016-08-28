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
            $this->_view->vacancies = $this->_model->getVacancies(["page" => -1]);
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
        $params = [
            "page" => paginationHelper::getCurrentPage(),
            "search" => $this->GetSearchParam()
        ];

        if($params['search']) {
            $response = array();
            $this->_model->GenerateCandidatesTableContent($params, $response);
            $response['success'] = 1;
            Response::ReturnJson($response);
        } else {
            $this->_view->vCount = $this->_model->CandidatesCount($params['search']);
            $this->_view->candidates = $this->_model->getCandidates($params);
            $this->_view->vacancies = $this->_model->getVacancies(["page" => -1]);
            $this->_view->render();
        }
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

                            $this->_model->GenerateCandidatesTableContent(0, $response);
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
    public function actionSearchInCandidates()
    {
        $response = array();
    }

    public function actionVacancyInfo()
    {
        $this->_view->vacancy = $this->_model->getVacancy($_GET['id']);
        $this->_view->assignedCandidates = [];
        $this->_view->render();
    }
    public function actionVacancies()
    {
        $params = [
            "page" => paginationHelper::getCurrentPage(),
            "search" => $this->GetSearchParam()
        ];

        if($params['search']) {
            $response = array();
            $this->_model->GenerateVacanciesTableContent($params, $response);
            $response['success'] = 1;
            Response::ReturnJson($response);
        } else {
            $this->_view->users = $this->_model->getCandidates(["page" => -1]);
            $this->_view->vacancies = $this->_model->getVacancies($params);
            $this->_view->vCount = $this->_model->VacanciesCount($params['search']);
            $this->_view->render();
        }
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

                if(!$response['warning'] = $this->_model->FindVacancy($vacancy))
                {
                    if($this->_model->AddVacancy($vacancy, $_POST['candidate_id']))
                    {
                        $response['success'] = 1;

                        $this->_model->GenerateVacanciesTableContent(["page"=>0],$response);
                    } else
                        $response['error'] = $this->_model->getDBError();
                }
            } else
                $response['warning'] = 'Empty fields!';

            Response::ReturnJson($response);
        }
    }
    public function actionEditVacancy()
    {
        if($this->_model->UpdateVacancy(new Vacancy($_POST)))
            Response::ReturnJson(['success'=>1]);
    }

    public function actionJournal()
    {

    }

    public function actionDeleteCandidate()
    {
        $response = $this->_model->Delete('candidates', $_GET['id']);
        if($response['success'] == 1) {
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

    function GetSearchParam()
    {
        return $_GET[Config::SEARCH_STRING] ?? false;
    }
}