<?php

class page_stock_issue extends Page{
	function init(){
		parent::init();

		$cols=$this->add('Columns');

		$col1=$cols->addColumn(4);
		$col2=$cols->addColumn(6);
		$col1->add('H4')->set('Issue Stock');
		$col2->add('H4')->set('Recent Issue Activity');
		$form=$col1->add('Form');
		$staff_field=$form->addField('autocomplete/Basic','staff')->validateNotNull();//->setEmptyText('Please Select')->validateNotNull();
		$staff_field->setModel('Staff');
		$item_field=$form->addField('autocomplete/Basic','item')->validateNotNull();//->setEmptyText('Please Select')->validateNotNull();
		$item_model=$this->add('Model_Stock_Item');
		$item_field->setModel($item_model);
		$form->addField('line','qty')->validateNotNull();//->setEmptyText('Please Select')->validateNotNull();
		$form->addField('text','narration');//->setEmptyText('Please Select')->validateNotNull();
		$form->addSubmit('Issue');

		$grid=$col2->add('Grid');
		$issue_model=$this->add('Model_Stock_Transaction');
		$issue_model->setOrder('id','desc');
	
		$issue_model->addCondition('type','Issue');

		$issue_model->getElement('created_at')->system(true);
		$grid->setModel($issue_model,array('item','qty','staff','issue_date'));


		if($form->isSubmitted()){

			$item=$this->add('Model_Stock_Item');
			$item->load($form['item']);

			$staff=$this->add('Model_Staff');
			$staff->load($form['staff']);

			// throw new Exception($staff['name'], 1);
			

			try {
				$this->api->db->beginTransaction();
			    $item->issue($staff,$item,$form['qty'],$form['narration'],$form);
			    $this->api->db->commit();
			} catch (Exception_ValidityCheck $e) {
				$form->displayError($e->getField(),$e->getMessage());
			   	$this->api->db->rollBack();
			   	throw $e;
			}

			$form->js(null,array($grid->js()->reload(),$grid->js()->univ()->successMessage('Item Issue Successfully')))->reload()->execute();



		}
		

		

		
	}
}