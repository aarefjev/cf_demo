# Test Project CF - Arte #

## Few Questions First ##
 - How many simultaniuos users do we expect (at the peak)?
 - From what locations? All other the world?
 - How much load to expect?
 -  How much level of the security is needed? Is the data coming from the users or from the "trusted" sources? Do we have to check for validity of the requests? 
 - Is it going to be one client and lot's of requests or messages from all over the world from thousands of clients? (From Mobile phones apps for example) or is it one (or only few) payment service providers that will feed us those transfer messages? This is really important to find where our bottlenecks are. 
  
  Many users - bottleneck is most likely number of sockets (if we are taking sockets approach and by default php has a limit of 1024 sockets, as far as I remember) and we have to think about load balancing and for socket's it's not a trivial "install AWS Loadbalancer and set an autoscaling group" task. 
  
  We can have a bunch of small servers with nginx/PHP-FPM  across all regions in Amazon/Rackspace/etc... doesn't matter. (btw from my experience Apache works with PHP5-FPM just as good in FastCGI mode and a lot easier to maintain for medium projects).
  
  If we have only few providers connected to our socket server and sending thousands requests per second and we opted the "easy" disk saving technique - how many IOPs our hdd on a server have?
  May be we should upgrade to SSD (10k IOPs vs SATA 150 IOPS) in the first place? Or use Ram Drives with millions of IOPs? What are we going to do with those saved files? may be it's better to use NoSQL solution or Memcache or NoSQL inerface to MySQL - MySQLnd or HandlerSocket  (for easier further data manipulations/access or simplicity of use).
  Do we really want to scale horisontally or it might be better to have server in each region and scale vertically as we grow? Too many possibilities, too many unknown...
  
 
## My Assumptions & why I did chose that approach and not another ##
	Anyway... I am going to assume lot's of things and make few predictions - so here they are:
	 - This is role for PHP developer, so I am not going to jump into node.js (might be a good thing - but some load testing is really needed) or anything else (D - seems an interesting language btw, especially if you writing servers ;) 
	 - I'll try to use PHP everywhere it's possible
	 - Will write some basic Consumtion/Processor/Frontend in "easy mode" and "Average/Hard" - just want to do a few tests to see how they perform and to compare speed (more for fun really)
	 - I am not going to write tests for my code (aka PHPUnit tests), this is not a production code - a nobody is going to support it in the future ;)
	 - will put as much comments into a code as possible
	 - not going to use any PHP Framworks (CodeIgniter, Zend or Laravel) this is just a proof of cencept really
	 - basic approach (no sockets) might be a great solution in some cases - extremely scalable, very easy to maintain, nothing, but php is required and HHVM / KVM can be used, if required, but that just an option
	 - in "simple" approach going to very basic solution ******
	 - in "hard" will create solution based on a standard PHP-Sockets library, but will also provide unfinished, but conceptual code



## input information - with comments ##

	### Consumption
	_________
	Easy
	Consumed messages are written to disk for rendering in the frontend -> 

	Average
	You have implemented rate limiting in your message consumption component.

	Hard
	The message consumption component is the main piece of work you focus on, and can handle a large number of messages per second.

	
	### Processor
	__________

	Easy
	Carry out no processing, and let messages filter to frontend directly.

	Average
	Analyse incoming messages for trends, and transform data to prepare for a more visual frontend rendering, e.g. graphing currency volume of messages from one particular currency pair market (EUR/GBP).

	Hard
	Messages are sent through a realtime framework which pushes transformed data to a Socket.io frontend.

	### Frontend
	_____________
	Easy
	Render a list of consumed messages.
	
	Average
	Render a graph of processed data from the messages consumed.
	
	Hard
	Render a global map with a realtime visualisation of messages being processed.

	
	
	

 
 
Thoughts:
