MacBook Pro 15-inch Battery Replacement Program module
==============

Provides information on the eligibility of 15-inch MacBook Pro battery recall program - https://support.apple.com/15-inch-macbook-pro-battery-recall 

The client script reaches out to the Apple service site to determine eligibility.  To not stress that service, this script only reaches out to check eligibilty once per day on affected hardware.

The table provides the following information:

* datecheck (string) Last time eligibility was checked
* eligibility (string) Eligibility return value from Apple's SN checker

