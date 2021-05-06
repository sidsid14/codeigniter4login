<?php  namespace App\Models;

use CodeIgniter\Model;

class TimeTrackerModel extends Model{
    protected $table = 'docsgo-time-tracker';
    protected $allowedFields = ['user_id', 'tracker_date', 'action_list'];
}