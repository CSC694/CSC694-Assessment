<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Plans\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;


class PlansController extends AbstractActionController
{

   protected $tableResults;

// Sample dump logic used for debugging, use as needed   
//      foreach ($this->getDatabaseData()->getAllYears() as $data) :
//          var_dump($data);
//      endforeach;
//      exit();

//      var_dump($request);
//      exit();

   public function getDatabaseData()
   {
       if (!$this->tableResults) {
           $this->tableResults = $this->getServiceLocator()
                      ->get('Plans\Model\DatabaseSql');
       }
       return $this->tableResults;
   }
   
   public function getGenericQueries()
   {
      if (!$this->tableResults) {
           $this->tableResults = $this->getServiceLocator()
                                      ->get('Application\Model\AllTables');
        }
        return $this->tableResults;
   }
   
   public function getAction()
   {
      // get unit from id in url
      $unitChosen = $this->params()->fromRoute('id', 0);
      // get programs for that unit
      $results = $this->getGenericQueries()->getProgramsByUnitId($unitChosen);
      // iterate through results forming a php array
      foreach ($results as $result){
         $programData[] = $result;
      }
      // encode results as json object
      $jsonData = new JsonModel($programData);
      return $jsonData;     
   }
      
   public function indexAction()
   {
        $request = $this->getRequest();
        if ($request->isPost()) {
         
            $action = $request->getPost('action-menu');
            $unit = $request->getPost('unit-menu');
            $programs = $request->getPost('prog-menu');
            $year = $request->getPost('year-menu');
            
            // create session variable
		$planSession = new Container('planSession');
		$planSession->action = $action;
                $planSession->unit = $unit;
                $planSession->programs = $programs;
                $planSession->year = $year;
            
            
            if ($action == "View" || $action == "Modify") {
               return $this->redirect()->toRoute('plans', array('action'=>'listplans'));                
            }
            else {
               return $this->redirect()->toRoute('plans', array('action'=>'addplan'));
            }
         }
         else {
            // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }
            // pass array to view
            return new ViewModel(array(
               'units' => $unitarray,
               'years' => $yeararray,
            ));
         }
         
   }
   
   public function listPlansAction()
   {
      $request = $this->getRequest();
            
        if ($request->isPost()) {

         }
         else {
            
            // get session data
       		$planSession = new Container('planSession');
		$action = $planSession->action; 
                $unit = $planSession->unit;
                $programs = $planSession->programs;
                $year = $planSession->year; 
            
            // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }         
                  
            // pass array to view
            return new ViewModel(array(
               'units' => $unitarray,
               'years' => $yeararray,
                              
               'action' => $action,
               'unit' => $unit,
               'programs' => $programs,
               'year' => $year,
            
               // get outcome and plans data
               'outcomes' => $this->getGenericQueries()->getOutcomes($unit, $programs, $year),
               'plans' => $this->getGenericQueries()->getPlans($unit, $programs, $year),   
            ));
         }
   }


   public function viewOnlyPlanAction()
   {
      // pull data from the route url
      $planId = (int) $this->params()->fromRoute('id', 0);                      
            
      $request = $this->getRequest();
      if ($request->isPost()) {
         /* perfomr post request action here */   
      }
      else {
                           // get session data
       		$planSession = new Container('planSession');
		$action = $planSession->action; 
                $unit = $planSession->unit;
                $programs = $planSession->programs;
                $year = $planSession->year;
         
                     // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }
            
         // Initial Page Load, get request
         return new ViewModel(array(
            'units' => $unitarray,
            'years' => $yeararray,
               
            'planId' => $planId,
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            
            'outcomes' => $this->getGenericQueries()->getOutcomesByPlanId($planId),
            'plan' => $this->getGenericQueries()->getPlanByPlanId($planId),
         ));
      }
   }
   
   
   public function modifyPlanAction()
   {
      // pull data from the route url
      $planId = (int) $this->params()->fromRoute('id', 0);
      
      $request = $this->getRequest();      
      
      if ($request->isPost()) {
          
         $button = $request->getPost('formSubmit');             
      
         if ($button == "formSavePlan" || $button == "formSaveDraft") {

            // set the draft flag
            $draftFlag = "N";
            if ($button == "formSavePlan") {
               $draftFlag = "Y";
            }
         
            $planId = $request->getPost('planId');
            $assessmentMethod = $request->getPost('textAssessmentMethod');
            $population = $request->getPost('textPopulation');
            $sampleSize = $request->getPost('textSamplesize');
            $assessmentDate = $request->getPost('textAssessmentDate');
            $cost = $request->getPost('textCost');
            $analysisType = $request->getPost('textAnalysisType');
            $administrator = $request->getPost('textAdministrator');
            $analysisMethod = $request->getPost('textAnalysisMethod');
            $scope = $request->getPost('textScope');
            $feedback = $request->getPost('textFeedback');
            $feedbackFlag = $request->getPost('textFeedbackFlag');
            $planStatus = $request->getPost('textPlanStatus');

            $this->getDatabaseData()->updatePlan($planId,$assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$analysisType,$administrator,$analysisMethod,$scope,$feedback,$feedbackFlag,$planStatus,$draftFlag);
            return $this->redirect()->toRoute('plans');
         }
         else {
            return $this->redirect()->toRoute('plans');
         }
      }
      else {
               // get session data
       		$planSession = new Container('planSession');
		$action = $planSession->action; 
                $unit = $planSession->unit;
                $programs = $planSession->programs;
                $year = $planSession->year;
         
                     // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }
            
         // Initial Page Load, get request
         return new ViewModel(array(
            'units' => $unitarray,
            'years' => $yeararray,
               
            'planId' => $planId,
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            
            'outcomes' => $this->getGenericQueries()->getOutcomesByPlanId($planId),
            'plan' => $this->getGenericQueries()->getPlanByPlanId($planId),
         ));
      }
   }
   
   public function addplanAction()
   {
       
      $request = $this->getRequest();
      
      if ($request->isPost()) {
         
         // get session data
       	 $planSession = new Container('planSession');	 
         $year = $planSession->year;
         
         // get button form data       
         $button = $request->getPost('formSubmit');
                                         
         if ($button == "formSavePlan" || $button == "formSaveDraft") {

            $metaFlag = $request->getPost('metaFlag');
            $outcomeId = $request->getPost('radioOutcomes');

            if ($metaFlag == "yes") {
               
               //set session variable
               $planSession = new Container('planSession');	 
               $planSession->outcomeId = $outcomeId;

               return $this->redirect()->toRoute('plans', array('action'=>'addplanmeta'));
            }
            else {
         
               // set the draft flag
               $draftFlag = "N";
               if ($button == "formSaveDraft") {
                  $draftFlag = "Y";
               }
            
               // get form data    
               $assessmentMethod = $request->getPost('textAssessmentMethod');
               $population = $request->getPost('textPopulation');
               $sampleSize = $request->getPost('textSamplesize');
               $assessmentDate = $request->getPost('textAssessmentDate');
               $cost = $request->getPost('textCost');
               $analysisType = $request->getPost('textAnalysisType');
               $administrator = $request->getPost('textAdministrator');
               $analysisMethod = $request->getPost('textAnalysisMethod');
               $scope = $request->getPost('textScope');
               $feedback = $request->getPost('textFeedback');
               $feedbackFlag = $request->getPost('textFeedbackFlag');
               $planStatus = $request->getPost('textPlanStatus');
                                 
               // insert into plan table and obtain the primary key of the insert
               $planId = $this->getDatabaseData()->insertPlan(0, "", $year, $assessmentMethod,$population,$sampleSize,$assessmentDate,$cost,$analysisType,$administrator,$analysisMethod,$scope,$feedback,$feedbackFlag,$planStatus,$draftFlag);                  
              
               // insert into the outcome table
               $this->getDatabaseData()->insertPlanOutcome($outcomeId, $planId['maxId']);
                     
               return $this->redirect()->toRoute('plans');
            }
         }
      }
      else {
               // get session data
       		$planSession = new Container('planSession');
		$action = $planSession->action; 
                $unit = $planSession->unit;
                $programs = $planSession->programs;
                $year = $planSession->year;
         
                     // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }
            
         // Initial Page Load, get request
         return new ViewModel(array(
            'units' => $unitarray,
            'years' => $yeararray,
               
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            
            'outcomes' => $this->getGenericQueries()->getUniqueOutcomes($unit, $programs, $year),
         ));
      }
   }
   
   public function addplanmetaAction()
   {
               
      $request = $this->getRequest();
      
      if ($request->isPost()) {
         
         // get session data
       	 $planSession = new Container('planSession');	 
         $year = $planSession->year;
         
         // get button form data       
         $button = $request->getPost('formSubmitMeta');
                                         
         if ($button == "formSavePlan" || $button == "formSaveDraft") {

            $outcomeId = $request->getPost('outcomeId');
          
            // set the draft flag
            $draftFlag = "N";
            if ($button == "formSaveDraft") {
               $draftFlag = "Y";
            }
               // get form data    
               $metaDescription = $request->getPost('textMetaDescription');
                                 
               // insert into plan table and obtain the primary key of the insert
               $planId = $this->getDatabaseData()->insertPlan(1, $metaDescription, $year, "","","","","","","","","","","","",$draftFlag);                  
              
               // insert into the outcome table
               $this->getDatabaseData()->insertPlanOutcome($outcomeId, $planId['maxId']);
                     
               return $this->redirect()->toRoute('plans');
         }
      }
      else {
               // get session data
       		$planSession = new Container('planSession');
		$action = $planSession->action; 
                $unit = $planSession->unit;
                $programs = $planSession->programs;
                $year = $planSession->year;
                $outcomeId = $planSession->outcomeId;
         
                     // Initial Page Load, get request
            // get units
            $results = $this->getGenericQueries()->getUnits();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $unitarray[] = $result;
            }
           
            // get years
            $results = $this->getGenericQueries()->getYears();
            // iterate over database results forming a php array
            foreach ($results as $result){
               $yeararray[] = $result;
            }
            
         // Initial Page Load, get request
         return new ViewModel(array(
            'units' => $unitarray,
            'years' => $yeararray,
               
            'action' => $action,
            'unit' => $unit,
            'programs' => $programs,
            'year' => $year,
            'outcomeId' => $outcomeId,
         ));
      }
   }
}
