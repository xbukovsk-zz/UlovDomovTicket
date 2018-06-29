<?php
/**
 * Created by PhpStorm.
 * User: vaclav
 * Date: 28.6.18
 * Time: 22:26
 */

namespace Models;


class UserAdminModel
{

    /**
     * @var \Models\VillageModel
     */
    private $villageModel;


    /**
     * @var \Nette\Database\Connection
     */
    private $db;


    private $privilegies = [
        0 => "addressbook",
        1 => "search"
    ];


    /**
     * @param \Models\VillageModel $villageModel
     * @param \Nette\Database\Connection $db
     */
    public function __construct(
        \Models\VillageModel $villageModel,
        \Nette\Database\Connection $db
    ) {
        $this->villageModel = $villageModel;
        $this->db = $db;
    }


    /**
     * Nastaveni neomezenych prav uzivateli
     *
     * @param string $user
     * @param string $privilegy
     */
    private function addFullPrivilegies(
        string $user,
        string $privilegy
    ) {
        $this->db->query("
            DELETE FROM User_admin WHERE user = $user AND privilegy = $privilegy
        ");

        $this->db->query("
            INSERT INTO User_admin", [
            'user'      => $user,
            'rule'      => $privilegy,
            'villageID' => NULL
        ]);
    }


    /**
     * Nastaveni jednoho opravneni k jednomu mestu
     *
     * @param string $user
     * @param string $privilegy
     * @param int $villageID
     */
    private function addSeparatePrivilegy(
        string $user,
        string $privilegy,
        int $villageID
    ) {
        $this->db->query("
            INSERT INTO User_admin", [
            'user'      => $user,
            'rule'      => $privilegy,
            'villageID' => $villageID
        ]);
    }


    /**
     * Ziskani jednoho uzivatelskeho opravneni
     *
     * @param string $user
     * @param string $privilegy
     * @param int $villageID
     */
    private function getUserCityPrivilegy(
        string $user,
        string $privilegy,
        int $villageID
    ) {
        return $this->db->fetch("
            SELECT * FROM User_admin
            WHERE user = $user AND rule = $privilegy AND villageID = $villageID
        ");
    }


    /**
     * Smazani uzivatelskeho opravneni
     *
     * @param string $user
     * @param string $privilegy
     * @param int $villageID
     */
    private function deleteUserCityPrivilegy(
        string $user,
        string $privilegy,
        int $villageID
    ) {
        $this->db->query("
            DELETE FROM User_admin
            WHERE user = $user AND rule = $privilegy AND villageID = $villageID
        ");
    }


    /**
     * Nastavovani uzivatelskych prav
     *
     * @param string $user
     * @param array $privilegies
     */
    public function set(
        string $user,
        array $privilegies
    ) {
        // Kombinace prav pomoci checkboxu
        // [ addressbook => [ 1 => true, 2 => false ] , search => [ 1 => false, 2 => false ] ], kde 1 a 2 jsou ID Praha a Brno.
        // Pokud bude formular kompletne nezaskrtnuty -> ziska kompletni prava
        // Pokud bude pro jedno pravo (Adresar) cely sloupec mest nezaskrtnuty, tak ziska pravo ke vsem mestum
        // Lze resit i pomoci dvojnasobneho foreach namisto array metod
        foreach ($privilegies as $key => $value) {
            $haystack = array_values($value);
            $all = in_array(true , $haystack) ? false : true;

            if ($all) {
                $this->addFullPrivilegies($user, $key);
            } else {
                foreach ($value as $village => $priv) {
                    $userPrivilegy = $this->getUserCityPrivilegy($user, $key, $village);

                    if ($priv && !$userPrivilegy) {
                        $this->addSeparatePrivilegy($user, $key, $village);
                    } elseif (!$priv && $userPrivilegy) {
                        $this->deleteUserCityPrivilegy($user, $key, $village);
                    }
                }
            }
        }
    }


    /**
     * Vrati pole jez bude obsahovat mesta, kam ma uzivatel pravo
     * Pokud bude mit uzivatel prava na vsechna mesta, tak bude mit ve villages NULL
     * Tim odpadnou veskere starosti s nastavovanim prav pri pridani User nebo Village
     * Diky tomu se pri fetchAll musi volat LEFT JOIN namisto INNER JOIN
     *
     * @param string $user
     * @param string $rule
     * @return array
     */
    public function get(
        string $user,
        string $rule
    ) {
        $result = $this->db->fetchAll("
            SELECT ua.ID, ua.villageID AS villages, v.name AS villageName
            FROM User_admin ua
            LEFT JOIN Village v ON ua.villageID = v.ID
            WHERE ua.user = $user AND rule = $rule"
        );

        if (!empty($result)) {
            if ($result[0]['villages'] === NULL) {
                return $this->villageModel->get();
            } else {
                $villages = [];

                foreach ($result as $row) {
                    $villages[] = $row['villageName'];
                }

                return $villages;
            }
        } else {
            return [];
        }
    }


    /**
     * Pridani noveho uzivatele
     *
     * @param string $name
     */
    public function addUser(
        string $name
    ) {
        foreach ($this->privilegies as $privilegy) {
            $this->db->query("
                INSERT INTO User_admin", [
                'user'      => $name,
                'rule'      => $privilegy,
                'villageID' => NULL
            ]);
        }
    }


    /**
     * Pridani noveho mesta
     *
     * @param string $name
     */
    public function addVillage(
        string $name
    ) {
        $this->db->query("
             INSERT INTO Village", [
             'name' => $name
        ]);
    }
}