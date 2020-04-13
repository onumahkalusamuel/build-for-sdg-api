<?php

function covid19ImpactEstimator($data)
{
  //decode the input data
  $input = $data;

  //initialize the output data container
  $output = [];
  $output['data'] = $input;
  $output['impact'] = [];
  $output['severeImpact'] =[];

  //currently infected for impact and severe impact
  $output['impact']['currentlyInfected'] = $input['reportedCases'] * 10;
  $output['severeImpact']['currentlyInfected'] = $input['reportedCases'] * 50;

  //get the covid19 infection factor
  $covid19InfectionFactor = covid19InfectionFactor($input['periodType'], $input['timeToElapse']);
  if(empty($covid19InfectionFactor)) die(); //end script if $covid19InfectionFactor is empty

  //calculate infectionsByRequestedTime
  $output['impact']['infectionsByRequestedTime'] = $output['impact']['currentlyInfected'] * pow(2, $covid19InfectionFactor);
  $output['severeImpact']['infectionsByRequestedTime'] = $output['severeImpact']['currentlyInfected'] * pow(2, $covid19InfectionFactor);


  //calculate severeCasesByRequestedTime
  $output['impact']['severeCasesByRequestedTime'] = (int) ($output['impact']['infectionsByRequestedTime'] * 0.15);
  $output['severeImpact']['severeCasesByRequestedTime'] = (int) ($output['severeImpact']['infectionsByRequestedTime'] * 0.15);

  //expected available beds
  $expectedAvailableBeds = $input['totalHospitalBeds'] - ((int)(0.65 * $input['totalHospitalBeds']));

  // hospital Beds By Requested Time
  $output['impact']['hospitalBedsByRequestedTime'] = (int) ($expectedAvailableBeds - $output['impact']['severeCasesByRequestedTime']);
  $output['severeImpact']['hospitalBedsByRequestedTime'] = (int) ($expectedAvailableBeds - $output['severeImpact']['severeCasesByRequestedTime']);

  //cases For ICU By Requested Time
  $output['impact']['casesForICUByRequestedTime'] = (int) ($output['impact']['infectionsByRequestedTime'] * 0.05);
  $output['severeImpact']['casesForICUByRequestedTime'] = (int) ($output['severeImpact']['infectionsByRequestedTime'] * 0.05);

    //cases For Ventilators By Requested Time
    $output['impact']['casesForVentilatorsByRequestedTime'] = (int) ($output['impact']['infectionsByRequestedTime'] * 0.02);
    $output['severeImpact']['casesForVentilatorsByRequestedTime'] = (int) ($output['severeImpact']['infectionsByRequestedTime'] * 0.02);
  
    //dollars in flight
    $output['impact']['dollarsInFlight'] 
    = (int) (
      ($output['impact']['infectionsByRequestedTime'] 
      * $input['region']['avgDailyIncomePopulation'] 
      * $input['region']['avgDailyIncomeInUSD']) 
      / covid19TimeToElapseInDays($input['periodType'], $input['timeToElapse'])
    );
    
    $output['severeImpact']['dollarsInFlight'] 
    = (int) (
      ($output['severeImpact']['infectionsByRequestedTime'] 
      * $input['region']['avgDailyIncomePopulation'] 
      * $input['region']['avgDailyIncomeInUSD']) 
      / covid19TimeToElapseInDays($input['periodType'], $input['timeToElapse'])
    );
  

  return $output;
}

function covid19InfectionFactor($periodType, $timeToElapse) 
{
  $timeToElapseInDays = covid19TimeToElapseInDays($periodType, $timeToElapse);
  if(empty($timeToElapseInDays)) return false;
  return (int) ($timeToElapseInDays / 3);
}

function covid19TimeToElapseInDays($periodType, $timeToElapse)
{
  switch ($periodType) {
    case 'days': { $multiplicationFactor = 1; break; }
    case 'weeks': { $multiplicationFactor = 7; break; }
    case 'months': { $multiplicationFactor = 30; break; }
    default: { $multiplicationFactor = null; break; }
  }
  return $multiplicationFactor * $timeToElapse;
}