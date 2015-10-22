# Order@Cloud
## What is it?
Order@Cloud is a Framework to sort Virtual Machines in Clouds with Multiple Objectives.
It gives you the possibility to easily implement Rules, Qualifiers and Costs in order to organize your Cloud.

It will receive the current placements of your Cloud (current Scenario) and, based on your customization, it will propose a better Scenario.

## How to use it?
1. Download the [OrderAtCloud.phar](https://github.com/arthurd2/order-at-cloud/raw/master/build/OrderAtCloud.phar)
2. Follow the example below
3. Enjoy the Cloud
```php
	//Load the Framework
	require_once "OrderAtCloud.phar";

	//Load your Rules, Qualifiers and/or Costs
	require_once "YourRules.php";
	require_once "YourQualifiers.php";
	require_once "YourCosts.php";
	
	//Build your Scenario
	$scenario = [];
	Cvmp::addVm($scenario, 'VM1','PM1');
	Cvmp::addVm($scenario, 'VM2','PM1');
	Cvmp::addVm($scenario, 'VM3','PM2');
	Cvmp::addVm($scenario, 'VM4','PM2');
	
	//Do the Magic!
	$oc = new OrderCloud($scenario);
	$bestScenario = $oc->organize($scenario);
```

## The Concept Behind
### It's elements
Basically the Framework has 3 implementable elements:
- Rules
- Qualifiers
- Cost

#### But first... let's clarify some things
1. A Placement is defined as a possible relation between a VM and a PM, guest and host respectively.
2. A Scenario (a.K.a Cloud VM Placement -- CVMP) is a set of placements which defines where each VM is placed on the Cloud, representing the real Cloud scenario or a possible one.

#### Rules
Rules  define placement constraints, i.e. rules can forbid VMs to be placed in PMs.

##### Rules Free of Context - RFC
RFC define whether a given VM can be hosted by a given PM and RSC define whether a given scenario (CVMP) is valid or not, i.e. RFC validates placements and RSC validates Scenarios. 
For instance, a VM A must be placed in a PM with processors of architecture ARM.

##### Rules Sensitive to the Context - RSC
RSC define whether a given scenario is valid or not, i.e. RSC validates Scenarios. 
The context here refers to a given Scenario, which is the context where the VMs are placed. 
For instance, a VM A should not be hosted in the same PM as a VM B.


#### Qualifiers
They are functions used to assess the quality of one or more placements of a Scenario, e.g. resource optimizers, high-availability strategies, objectives' evaluations. 
These assessments will enable the comparison and, consequently, selection of Scenarios.

#### Cost
The cost is a function which quantifies the implementation cost between two Scenarios, a root and a target scenario.
Its values disregard units and can vary from the number of migrations to the required resource to implement the target scenario.
