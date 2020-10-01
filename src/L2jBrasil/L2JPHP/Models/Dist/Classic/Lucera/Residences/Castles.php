<?php
namespace L2jBrasil\L2JPHP\Models\Dist\Classic\Lucera\Residences;



use L2jBrasil\L2JPHP\Models\AbstractBaseModel;
use L2jBrasil\L2JPHP\Models\Dist\Classic\Lucera\Players\Characters;

class Castles extends AbstractBaseModel implements  \L2jBrasil\L2JPHP\Models\Interfaces\Residences\Castles
{

    protected $_table = 'castle';
    protected $_primary = 'id';
    protected $_tableMap = [
        "tax" => "tax_percent",
        "vault" => "treasury",
    ];


    public function status($castleId)
    {
        return $this->select(["castle.id",
            "castle.name as castle",
            "from_unixtime(castle.siege_date/1000) as siege_date",
            "castle.tax_percent AS tax",
            "leader.char_name leader",
            "clan_pledge.name as clan_name",
            "ally.ally_name"], $this->getTableName())
            ->joinLeft("clan_data as clan", "clan.hasCastle = castle.id")
            ->joinLeft("clan_subpledges as clan_pledge","clan_pledge.clan_id = clan.clan_id and clan_pledge.type = 0")
            ->joinLeft("ally_data as ally","clan.ally_id = ally.ally_id")
            ->joinLeft("characters as leader","leader.obj_Id = clan_pledge.leader_id")
            ->where("castle.id = '{$castleId}'") //you may use bind here
            ->query()
            ->Fetch();
    }

    public function siege()
    {

        return $this->select(["castle.id",
				"castle.name as castle",
				"from_unixtime(castle.siege_date/1000) as siege_date",
				"castle.tax_percent AS tax",
				"leader.char_name leader",
				"clan_pledge.name as clan_name",
				"ally.ally_name"], $this->getTableName())
            ->joinLeft("clan_data as clan", "clan.hasCastle = castle.id")
            ->joinLeft("clan_subpledges as clan_pledge","clan_pledge.clan_id = clan.clan_id and clan_pledge.type = 0")
            ->joinLeft("ally_data as ally","clan.ally_id = ally.ally_id")
            ->joinLeft("characters as leader","leader.obj_Id = clan_pledge.leader_id")
            ->query()
            ->FetchAll();



    }

    public function siegeParticipants($castleId)
    {
        /**
         *
        SELECT
        siege_clans.type,
        clan_pledge.name as clan_name
        FROM siege_clans
        left join clan_data as clan on clan.clan_id = siege_clans.clan_id
        left join clan_subpledges as clan_pledge on clan_pledge.clan_id = clan.clan_id and clan_pledge.type = 0
        WHERE
        siege_clans.residence_id = 4;
         */

        return $this->select(["siege_clans.type",
            "clan_pledge.name as clan_name"], "siege_clans")
            ->joinLeft("clan_data as clan", "clan.clan_id = siege_clans.clan_id")
            ->joinLeft("clan_subpledges as clan_pledge","clan_pledge.clan_id = clan.clan_id and clan_subpledges.type = 0")
            ->where("siege_clans.residence_id = {$castleId}")
            ->query()
            ->FetchAll();
    }
}