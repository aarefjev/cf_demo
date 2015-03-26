# Test Project CF - Arte #

# Major disclaimer - I am really sorry, some of the code was not tested properly, some of the code is simply replaced with a big comments - my biggest problem here - my free time, I have none. All thanks to my 1 year old daughter :(

## Few Questions First ##
 - How many simultaneous users do we expect (at the peak)?
 - From what locations? All other the world?
 - How much load to expect?
 -  How much level of the security is needed? Is the data coming from the users or from the "trusted" sources? Do we have to check for validity of the requests? 
 - Is it going to be one client and lot's of requests or messages from all over the world from thousands of clients? (From Mobile phones apps for example) or is it one (or only few) payment service providers that will feed us those transfer messages? This is really important to find where our bottlenecks are. 
  
  Many users - bottleneck is most likely number of sockets (if we are taking sockets approach and by default php has a limit of 1024 sockets as far as I remember, but we can always recompile PHP with a different number in mind) and we have to think about load balancing and for socket's it's not a trivial "install AWS Load balancer and set an auto scaling group" task. 
  
  We can have a bunch of small servers with nginx/PHP-FPM  across all regions in Amazon/Rackspace/etc... doesn't matter. (btw from my experience Apache works with PHP5-FPM just as good in FastCGI mode and a lot easier to maintain for medium projects).
  
  If we have only few providers connected to our socket server and sending thousands requests per second and we opted the "easy" disk saving technique - how many IOPs our hdd on a server have?
  May be we should upgrade to SSD (10k IOPs vs SATA 150 IOPS) in the first place? Or use Ram Drives with millions of IOPs? What are we going to do with those saved files? may be it's better to use NoSQL solution or Memcache or NoSQL interface to MySQL - MySQLnd or HandlerSocket  (for easier further data manipulations/access or simplicity of use).
  Do we really want to scale horizontally or it might be better to have server in each region and scale vertically as we grow? Too many possibilities, too many unknown...
  
 
## My Assumptions & why I did chose that approach and not another ##
	Anyway... I am going to assume lot's of things and make few predictions - so here they are:
	 - This is role for PHP developer, so I am not going to jump into node.js (might be a good thing - but some load testing is really needed) or anything else (D - seems an interesting language btw, especially if you writing servers ;) 
	 - I'll try to use PHP everywhere it's possible
	 - Will write some basic Consumption/Processor/Frontend in "easy mode" and "Average/Hard" - just want to do a few tests to see how they perform and to compare speed (more for fun really)
	 - I am not going to write tests for my code (aka PHPUnit tests), this is not a production code - a nobody is going to support it in the future ;)
	 - will put as much comments into a code as possible
	 - not going to use any PHP Framworks (CodeIgniter, Zend or Laravel) this is just a proof of concept really
	 - basic approach (no sockets) might be a great solution in some cases - extremely scalable, very easy to maintain, nothing, but php is required and HHVM / KVM can be used, if required, but that just an option
	 - in "simple" approach going to be what seems very basic solution, but it's actually not ;) No sockets, etc. Not too much fancy stuff - but it will work pretty much everywhere and extremely scalable and easy to implement.
	 - in "hard" will create solution based on a standard PHP-Sockets library, but will also provide unfinished, but conceptual code - sorry, don't have too much free time here

	 
## Descriptions of the solutions:
	I used same loader for both of the solutions simple PHP + Curl message sender. Sending content as a POST date is a little non-conventional method on a web, but I came across that before in Authorise.NET and YuDo (I think).
	- ### "Easy"
		Step 1 - Consumption: Message received by PHP script using $HTTP_RAW_POST_DATA variable - in order for this to work in php 5.3+ (as far as I remember) you have to set none standard HTTP Header <-- done in Loader
		Step 2 - Processor: Separate JSON data files saved onto a hard drive (can be NSF folder or AmazonS3 bucket) into a separate folder/folders  - in my sample it's all in one folder, but some FS don't like to have too many files in one folder so we have to have a hierarchy of some sort ideally by /YEAR/MONTH/DATE/HOUR, etc. 
		Step 2.1: Because we need a list of files on a server (to render them we need path to each file and also if we want to show some trending data with graphs (with a help of some nice javascript library: pick one you like the most - http://www.sitepoint.com/15-best-javascript-charting-libraries/) it's probably a good idea to generate separate JSON (or few, for various currency pairs or countries, or all together) files with some major data in them, so we could load that data with a help of Ajax and process how we want (or send directly to js chart libs)
		Step 3 - Frontend: Actual rendering is easy - load JSON, process, send to Chart JS lib, if needed, or display content of the message. Anything you want.
		My sample will only have list of files and button next to each to show data that in the file. If we want updates (not in real time, but it's ok for most cases) - Ajax will run using settimeout every so often and make some changes in a frontend. 
		
	- ### "Advanced"
		Step 1.0: We have one main server (written in PHP this time), sitting there and waiting for socket connections to open and send us message (or multiple messages).
		Step 1.1: Rate limiter - after every message received we check how many messages we received in the past second (or minute / depends on what limit we want to set) and if we exceeded limit -> run microtime() in a loop until limit is not exceeded
		Step 1.2: There are few tricks in PHP how to make sockets a little bit faster and also secure - PHP can support good old BSD sockets - http://php.net/manual/en/intro.sockets.php, but... they don't support ssl/tls - and if we what it - better use Streams: http://php.net/manual/en/book.stream.php  And again - by default PHP can handle 1024 open connections but can be easily be recompiled to support more.
		Step 2: All messages can be saved to a disk in exactly same manner as in "Easy" approach.
		Step 2.1: Ideally all messages are saved in a Database SQL/NoSQL - it's all depends on a speed and number of clients. Let's assume the easiest - all messages are saved in MySQL for storage, we can easily get data by performing simple SQL statements Limit by time, number of records, group by currency pair's etc. Another option to keep major trends (if we know what is going to be required) in a Master Socket Server memory and retrieve data using special socket calls.
		Step 2.2: Socket.io frontend requires also a socket.io server - we can start dedicated socket.io server and send messages to it using soap client calls from our main server or start our own php server to simulate socket.io - it's probably easier to start existing socket.ie server and have a client part ;)
		Step 3: Good old (supported with almost all major browsers now!) WebSockets. Fast, reliable and so much fun (Since they require special handshake according to a WebSockets protocol), but one it's done very easy to use.
		Step 3.1: Some JavaScript chart libraries will be a good help here. Events from WebSockets will trigger updates on a graphs. Simple.
		Step 3.2: Pretty much same as above, but with great GoogleMap API - it can definitely do stuff like center map based on a country ISO code and we already have it in every JSON data file.

## input information - with comments ##

	### Consumption
	_________
	Easy
	Consumed messages are written to disk for rendering in the frontend

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
