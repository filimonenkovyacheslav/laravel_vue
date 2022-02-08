<?php

namespace App\Http\Models\Import;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Auth;
use DB;
use BaseModel;
use User;
use Role;

class ImportLink extends Model
{
	protected $table = 'import_links';

	public $fillable = [
		'author', 'link', 'status', 'status_time', 'run_id'
	];
	public function user()
	{
		return $this->belongsTo(User::class, 'author');
	}

	public static function getStatuses() {
		return [
			0 => __('Pending'),
			1 => __('Active'),
			2 => __('Rejected'),
			3 => __('Deleted'),
		];
	}

	public static function getImportLinks(&$params)
	{
		$select = 'import_links.id, import_links.author, import_links.status, import_links.link, import_links.run_id, r.status as run_status, r.ended, r.cnt_inserted+r.cnt_deleted+cnt_updated as cnt_props, cnt_errors';
		$links = static::select(DB::raw($select))->leftJoin('import_runs as r', 'r.id', '=', 'import_links.run_id');

		$user = Auth::user();
		if(!$user) return null;

		$admin = $user->isAdmin();
		if(!$admin) {
			$links->where([['import_links.author', $user->id], ['import_links.status', '!=', 3]]);
		}
		$orderBy = 'd_date';

		foreach($params as $k => $v) {
			switch($k) {
				case 'status':
					if(is_numeric($v)) {
						$links->where('import_links.status', $v);
					}
					break;
				case 'author_id':
					if(is_numeric($v) && $v > 0) {
						$links->where('author', $v);
						$author = User::where('users.id', '=', $v)->first();
						if($author) {
							$author = $author->toArray();
							$role = Role::find($author['role_id'])->name;
							$params['author'] = array_merge($author, User::getUserRelation($author['id'], $role));
						}
					}
					break;
				case 'order_by':
					if(!empty($v)) {
						$orderBy = $v;
					}
					break;
				default:
					break;
			}
		}
		switch($orderBy) {
			case 'a_date':
				$links->orderBy('id', 'asc');
				break;
			case 'd_date':
				$links->orderBy('id', 'desc');
				break;
			case 'link':
				$links->orderBy('import_links.link', 'asc');
			default:
				break;
		}
		$pagination = $links->with(['user'])->paginate(BaseModel::$pagination);
		$pagination->getCollection()->transform(function ($entity) use($admin) {
			return static::_afterGet($entity, $admin);
		});
		
		return $pagination;
	}

	public static function getAllImportLinks($id = 0)
	{
		$user = Auth::user();
		if(!$user) return null;

		$links = static::select(DB::raw('id, link'))->orderBy('id', 'desc');
		if(!$user->isAdmin()) {
			$links->where([['author', $user->id], ['status', '!=', 3]]);
		}
		$links = $links->get();
		return $links ? $links->toArray() : false;
	}

	public static function getImportLinkById($id)
	{
		$link = false;
		if($id && is_numeric($id)) {
			$link = static::where('id', $id)->with(['user'])->first();
		}
		return $link ? static::_afterGet($link, true) : false;
	}

	public static function addImportLink($link)
	{

		if(!isset($link) || empty($link) || static::where([['link', $link], ['status', '!=', 3]])->exists()) return;

		$user = Auth::user();
		$newLink = new ImportLink();
		$newLink->author = $user['id'];
		$newLink->link = $link;
		if($user->isAdmin()) {
			$newLink->status = 1;
			$newLink->status_time = DB::raw('now()');
		}
		$newLink->save();
		return;
	}

	public static function setImportLinkStatus($id, $status)
	{
		if(!is_numeric($status) || $status == 0 || !isset(static::getStatuses()[$status])) return;

		$user = Auth::user();
		$link = static::find($id);
		if(!$link) return;
		if(!$user->isAdmin() && ($status != 3 || $link['author'] != $user['id'])) return;
		$link->fill(['status' => $status, 'status_time' => DB::raw('now()')])->save();

		return;
	}

	public static function _afterGet($link, $getUser = false) {
		$linkData = !is_array($link) ? $link->toArray() : $link;

		if($getUser && isset($linkData['user']) && is_array($linkData['user'])) {
			$user = $linkData['user'];
			$role = Role::find($user['role_id'])->name;
			$linkData['user'] = array_merge($user, User::getUserRelation($user['id'], $role));
			$linkData['user']['type'] = $role;
			//$linkData['author_name'] = $user['first_name'].' '.$user['last_name'].' (ID '.$user['id'].')';
		}
		$linkData['status_label'] = static::getStatuses()[$linkData['status']];
		return $linkData;
	}
}
