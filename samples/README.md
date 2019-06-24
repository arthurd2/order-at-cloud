# Samples 
Here you will find the following samples:
* Costs
	* CostMemoryMigrations: Counts how much memory would be migrated
	* CostMigrations: Counts how many migrations would be made
	* CostOne: Dummy Cost Function
* Rules
	* Free of Context
		* RfcClusterCoherence: Checks if the migrated VM is in its own cluster.
		* RfcLiveMigration: Checks if the new host has the needed resources
		* RfcTime: Search Timeout Example
	* Sensitive to the Context
		* RscMaxCost: Checks if the new scenarios exceed the Max Cost preset
		* RscMemoryAvailability: Checks if the PM has memory enough to host the VM.
* Qualifiers
	* QuaConsolidatePm: Tries to reduce the number of active PMs and also equality distributes the VMs among the active PMs. 
	* QuaDistributeServices: Tries to distribute VMs from the same services in different PMs.
	* QuaDistributeStoragePool: Tries to distribute VMs that uses the same SAN different PMs.
