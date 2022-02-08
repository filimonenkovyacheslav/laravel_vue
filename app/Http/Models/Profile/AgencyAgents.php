<?php

namespace App\Http\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use User;
use BaseModel;
use Auth;

class AgencyAgents extends Model
{
	protected $table = 'agency_agents';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'agency_id',
		'agent_id',
		'status',
	];

	public static $statuses = [
		0 => 'pending',
		1 => 'active',
		2 => 'rejected',
		3 => 'deleted',
	];

	public static function _afterGet($entity, $role = null, $relation = null) {
		return $entity;
	}

	public static function getAgencyId($agent)
	{
		$row = static::where('agent_id', $agent)->where('status', '!=', 2)->get()->first();
		if($row) {
			return $row->agency_id;
		}
		return null;
	}

	public static function getAgency($agent, $role, $pending = false)
	{
		$agency = User::join('agency_agents', 'users.id', '=', 'agency_agents.agency_id')
			->where([['agency_agents.agent_id', $agent], ['users.status', 1]]);
		if($pending) {
			$agency->whereIn('agency_agents.status', [0, 1]);
		} else {
			$agency->where('agency_agents.status', 1);
		}
		$agency = User::_addRelation($agency, $role)->first();

		$agencyArr = !empty($agency) ? $agency->toArray() : [];
		$agencyArr = User::_afterGet($agencyArr, $role);
		return $agencyArr;
	}

	public static function saveAgency($agent, $agency, $status)
	{
		$current = static::where('agent_id', $agent)->whereIn('status', [0, 1])->get();
		$found = false;
		if($current) {
			foreach($current as $link) {
				if($link->agency_id == $agency) {
					$found = true;
					if($link->status != $status && $status == 1) {
						$link->fill(['status' => $status])->save();
					}
				} else {
					$link->delete();
				}
			}
		}
		if(!$found && $agency) {
			static::create(['agency_id' => $agency, 'agent_id' => $agent, 'status' => $status]);
		}
		return true;
	}

	public static function saveAgents($agency, $agents, $status)
	{
		$current = static::where('agency_id', $agency)->delete();
		if(!is_null($agents) && is_array($agents)) {
			foreach($agents as $agent) {
				static::create(['agency_id' => $agency, 'agent_id' => $agent, 'status' => $status]);
			}
		}
		return true;
	}

	public static function getAgentsForBackend($agency, $params = [])
	{
		$agents = static::select(['agency_agents.agent_id', 'agency_agents.status as agent_status', 'users.name', 'users.last_name', 'users.first_name', 'users.slug'])
			->join('users', 'users.id', '=', 'agency_agents.agent_id')
			->where([['agency_agents.agency_id', $agency], ['users.status', 1]])
			->whereIn('agency_agents.status', [0, 1]);

		if(isset($params['name'])) {
			//$agents->whereRaw('(users.first_name ILIKE \'%'. $params['name']. '%\' OR '.  'users.last_name ILIKE \'%' . $params['name'] . '%\')');
			$value = '%'. $params['name']. '%';
			$agents->whereRaw("(users.first_name ILIKE ? OR users.last_name ILIKE ?)", [$value, $value]);
		}

		//$agents = $agents->get();
		//$pagination = BaseModel::getPageData($agents, $params);
		$pagination = $agents->paginate(BaseModel::$pagination);

		return $pagination;
	}

	public static function getAgentsForFrontend($agency, $role, $params = [])
	{
		$agents = User::join('agency_agents', 'users.id', '=', 'agency_agents.agent_id')
			->where([['agency_agents.agency_id', $agency], ['users.status', 1], ['agency_agents.status', 1]]);
		$agents = User::_addRelation($agents, $role)->get();

		$agentsArr = !empty($agents) ? $agents->toArray() : [];

		foreach($agentsArr as $i => $agent) {
			$agentsArr[$i] = User::_afterGet($agent, $role);
		}

		return $agentsArr;
	}

	public static function countNewAgents($agency)
	{
		return static::join('users', 'users.id', '=', 'agency_agents.agent_id')
			->where('agency_agents.agency_id', $agency)
			->where('agency_agents.status', '=', 0)
			->where('users.status', 1)
			->count();
	}

	public static function setAgentStatus($agent, $status)
	{
		$agency = Auth::user()->id;
		$link = static::where('agency_id', $agency)->where('agent_id', $agent)->where('status', '!=', 2)->get()->first();

		if($link) {
			$link->fill(['status' => $status])->save();
			return true;
		}
		return false;
	}
}
