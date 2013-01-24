people-you-might-know
=====================

	Personal algorithm to find people you might know inspired by what I can feel on Facebook and Linkedin.
	I did it for  a technical test within 5 hours.
	I know this is not perfect, I'll try to improve it later.

How does it work?
=====================
	We can find people we might know with comparing the following criterias: 
		- Work
		- School
		- Location
		- Mutual friends
		- Profile visiting
		- Tagged pictures together
		- Age range

	I "emulate" a database in csv files
	Then I run the algo


Run the script
=====================
	You can launch it via a terminal:
		php index.php [user_id] [nb_show]
			[user_id] is the iduser's you want see possible match
			[nb_show] how many matches you want see

Algorithm
=====================
	
	- We give weight to each criteria (config.php)
	- We chose a user
	- I give a score to all the users in database. The higher score, the more you might know the guy
		
	Implementation:
		- Iterate on every users to find if the user match together on criterias:
		- Compare if the other user have the same: work, school, location
		- Count the number of common friends
		- Count the number the other visited my page
		- Count how many time we were tagged together on pictures
		- Calcultate the age range of people. We usually are friends with people who are the same age
		
		Of course the scoring is completely arbitrary. For example, I decide:
			If they were tagged together, I multiply the weight * number of picture together

	Some optimisations I did: 
		- setting the value as a key of the array and using isset instead of searching everytime in array is much faster (pymk.php, line 181, 103)
		- isset is faster than array_keys_exist
	Some optimisation I could do:
		- Only iterate one time on every user.
		- Find friends level 2
		- Find interrests of people
		- A better scoring system
		- How many comment the user publish on your account (if public profile)
		- If you're friend a long time with someone, we can suggest you some random friends

Recommandations for huge database:
=====================				
	
	- On bigger database we couldnt iterate on every users. The best choice is to limit it to level 3 connecition of the users.
	- Use memcached to store value, and avoid the repetitive databases requests.
	- A good code is not enough to support millions of users, a good server architecture is also required. I would suggest:
		Servers:
			- Server A:
				- With a CDN (akamai for example). The end user will get the statics content with the nearest server.
				- Use memcached on local, to have instant answer of the website and avoid ping
			- Server B: database 
			- Server C: Run the script. Take informations on
		How? : 
			The Server C copy the database from server B every x hours. 
			Then, it runs the "People you might Know" script and save the result in Server A (memcached)
			The server A just request with a simple known key getted in server B (we avoid big database request in Server B)
