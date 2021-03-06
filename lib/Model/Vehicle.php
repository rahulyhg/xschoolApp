<?php

class Model_Vehicle extends Model_Table{
	public $table="vehicles";
	function init(){
		parent::init();

		$this->hasOne('Vehicle_Type','vehicle_type_id');
		$this->hasOne('Branch','branch_id');
		$this->addField('code')->caption('Vehicle Number');
		$this->addField('capcity');
		$this->addField('driver_name');
		$this->addField('driver_number');
		$this->hasMany('Students','vehicle_id');


		$this->addExpression('name')->set(function ($m,$q){
			$vt= $m->add('Model_Vehicle_Type',array('table_alias'=>'mv'));
			return "(concat((".$vt->addCondition('id',$q->getField('vehicle_type_id'))->_dsql()->del('fields')->field('name')->render()."),'-',".$q->getField('code')."))";
		});



		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($name,$capcity,$driver_name,$driver_number,$branch=null){
		if($this->loaded())
			throw $this->exception("You can not use loaded Model");
		if(!$branch)
			$branch=$this->api->currentBranch;
		$this['name']=$name;
		$this['capcity']=$capcity;
		$this['driver_name']=$driver_name;
		$this['driver_number']=$driver_number;
		$this['branch_id']=$branch->id;
		$this->save();

		$log=$this->add('Model_Log');
		$log->createNew("vehicle created");
		$log->save();
		return true;
	}

	function student(){
		return $this->ref('Students');
	}

	function type(){
		return $this->ref('vehicle_type_id');
	}

	function filterByBranch($branch){
		$this->addCondition('branch_id',$branch->id);
		return $this;
	}
}