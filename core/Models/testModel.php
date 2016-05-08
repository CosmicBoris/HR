<?php

/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 22.02.2016
 * Time: 10:28
 */
class TestModel extends Model
{
    public function  FillUsers()
    {
        $names = array('Віта','Іра','Лана','Лена', 'Мар\'я', 'Міра', 'Настя'
        ,'Таня', 'Тіна', 'Олекса', 'Аарон', 'Августин', 'Альберт', 'Андрій', 'Аркадій','Богдан', 'Борис','Василь');

        $sur = array('Денисенко', 'Денис', 'Денисюк', 'Денищенко','Мартиненко', 'Мартинець',
            'Мартинюк', 'Марцинківський', 'Марцінків', 'Мартинович', 'Мартиновський','Романенко',
            'Романченко', 'Романюк', 'Романчук', 'Ромась', 'Ромасенко', 'Ромасюк', 'Ромащенко',
            'Ярофієнко', 'Ярош', 'Ярошенко', 'Ярошевський');

        $email = array('urk.net', 'gmail.com', 'i.ua', 'yandex.ru');
        $pass = 'dab6a23834183c8ab420a07421ebd034';

        set_time_limit(100);

        for($i = 1382; $i <= 3000;$i++ ) {
            $user = new User();
            $n = $names[rand(0, count($names)-1)];
            $user->UserLogin = $n.$i;
		    $user->UserPassword = $pass;
            $user->UserFullName = $n.' '.$sur[rand(0, count($sur)-1)];
		    $user->UserEmail = $n.$i.'@.'.$email[rand(0, count($email)-1)];
		    $user->InspectionID = rand(0, 10);
            $this->dbLink->AddUser($user, rand(3,6));
            echo $n.$i;
        }
    }

    public function FillEntitys()
    {
        function make_seed() {
            list($usec, $sec) = explode(' ', microtime());
            return (float) $sec + ((float) $usec * 100000);
        }

        Response::ReturnJson();
        $EDRPOU = array();
        $temp = $this->dbLink->ExecuteSql('SELECT `Entity_EDRPOU` FROM `Entity`');
        foreach($temp as $value){
            $EDRPOU[] = $value['Entity_EDRPOU'];
        }
        $arg = array();
        $arg['status'] = 1;
        $streets = array('Віта','Іра','Лана','Лена', 'Мар\'я', 'Міра', 'Настя'
        ,'Таня', 'Тіна', 'Олекса', 'Аарон', 'Августин', 'Альберт', 'Андрій', 'Аркадій','Богдан', 'Борис','Василь');

        $names = array('Денисенко', 'Денис', 'Денисюк', 'Денищенко','Мартиненко', 'Мартинець',
            'Мартинюк', 'Марцинківський', 'Марцінків', 'Мартинович', 'Мартиновський','Романенко',
            'Романченко', 'Романюк', 'Романчук', 'Ромась', 'Ромасенко', 'Ромасюк', 'Ромащенко',
            'Ярофієнко', 'Ярош', 'Ярошенко', 'Ярошевський');
        $fname = array('метал', 'бетон' ,'арматура','электрон','маш','ГЕС','Керпич','Авто');

        $email = array('urk.net', 'gmail.com', 'i.ua', 'yandex.ru');
        $phoneCodes = array('044', '063', '097', '032', '023');

        for($i = 0; $i < 1000;$i++ ) {
            $data = array();
            $data['Entity']['Entity_DRFO'] = rand(0,1);
            $n = ($data['Entity']['Entity_DRFO']) ? 8 : 10;

            mt_srand(make_seed());
            do{
                for($ii = 0; $ii < $n; $ii++){
                    $data['Entity']['Entity_EDRPOU'] .= mt_rand(0,9);
                }
            }while(in_array($data['Entity']['Entity_EDRPOU'], $EDRPOU));
            $EDRPOU[] = $data['Entity']['Entity_EDRPOU'];

            $data['Entity']['Entity_DateModified'] = date("Y-m-d H:i:s");
            $name = $names[rand(0, count($names)-1)];
            $data['Entity']['Entity_Name'] = $name;
            $data['Entity']['Entity_FullName'] = $name.$fname[rand(0, count($fname)-1)];
            $data['Entity']['Entity_DirectorId'] = 5;
            $data['Entity']['Entity_Phone'] = $phoneCodes[rand(0,count($phoneCodes)-1)]
                .'-'.rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9);
            $data['Entity']['Entity_Fax'] = $phoneCodes[rand(0,count($phoneCodes)-1)]
                .'-'.rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9);
            $data['Entity']['Entity_EMail'] = "uniquemail@entity.ua";
            $data['Entity']['Entity_KVED'] = $i;
            $data['Entity']['Entity_KOPFG'] = "KOP";
            $data['Entity']['Entity_Status'] = 1;
            $data['Entity']['Entity_UserId'] = Auth::GetUserID();
            $data['Entity']['Entity_Status'] = 1;
            $data['Entity']['Entity_lAddress'] = 4;
            $data['Entity']['Entity_pAddress'] = 4;
            $data['Entity']['Entity_Shtat'] = rand(1,5000);
            if($this->dbLink->insert('Entity', $data['Entity'])->RunQuery()){
                $this->dbLink->insert('entity_user',
                    array('EntityId'=> $this->dbLink->lastInsertedId(),
                        'UserId'=> $data['Entity']['Entity_UserId']))
                    ->RunQuery();
                $arg[$i] = 1;
            }else{
                $arg['error'.$i] = $this->dbLink->getErrors();
            }
        }
        echo json_encode($arg);
    }
}