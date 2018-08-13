# Project description

The goal of the project was to enhance the users component with two additional views, one that provides lists of users based 
on user groups and another one that displays the user details. All the changes were done taking care that the code complies with
the coding standards.
This addition solves two issues:

- The long outstanding problem that the user menu item is not working on the front end
- That we can decouple com_contact as you need com_contact to show a list of users on the front end.



# How to use it
In order to use the two new views the only thing to do, if you do not have Joomla! installed, is to clone the staging branch 
of this repository and to install Joomla!.

If you already have Joomla! 4.x installed, you have to merge the staging branch of this repository into yours. 
Then a database change is needed, that is the `access` column into the users table. This can be done using 

 ``` ALTER TABLE  `#__users` ADD COLUMN `access` int(10) unsigned NOT NULL DEFAULT 0; ```

The actual way to use the views is explained in this Joomla! Documentation page: [Users List and Details Views](https://docs.joomla.org/J4.x:Users_List_and_Details_Views#Introduction)



# My work
Below there is a list with the **pull requests** that I have done. The first one is the one that includes the most work, 
the rest are bug fixes and refactoring.

**Joomla! 4.0**
- **This is the PR that includes the two new views** - [PR #21441](https://github.com/joomla/joomla-cms/pull/21441)

- Repair router for Joomla! 4.x - [PR #21187](https://github.com/joomla/joomla-cms/pull/21187)
- Sql fix - schema parser expected format - [PR #20889](https://github.com/joomla/joomla-cms/pull/20889)
- Repair add new user note - [PR #20873](https://github.com/joomla/joomla-cms/pull/20873)
- Refactor JClasses for the users component so that every class is included using namespaces - [PR #20841](https://github.com/joomla/joomla-cms/pull/20841)

**Joomla! 3.x**
- Repair users router for Joomla! 3.x so that it takes into consideration edit layout - [PR #21274](https://github.com/joomla/joomla-cms/pull/21274)

**Blog posts.** During GSoC I wrote three blog posts that described my activity until that point.
- [First](https://community.joomla.org/gsoc-2018/joomla-gsoc-18-enhance-user-component.html)
- [Second](https://community.joomla.org/gsoc-2018/follow-up-about-the-enhance-user-component-project.html)
- [Third](https://community.joomla.org/gsoc-2018/enhance-user-component-project-part-3.html)
