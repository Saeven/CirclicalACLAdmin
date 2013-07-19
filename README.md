Circlical ACL Administrator
=========

![Dashboard image](https://raw.github.com/Saeven/CirclicalACLAdmin/master/examples/circlical-acl.jpg)

This is an ACL administration dashboard for use with Zend Framework 2 and ZFCUser + BjyAuthorize + Doctrine.  Where the former provide a great framework to implement access control, they offer no means to invite users (as is often the case with corp. applications).  Where ZFCUser and BjyAuthorize saved me a ton of time, this is my attempt to give back.

> Doctrine as a requirement is just happenstance right now, I relied on access to object repo for DB connections for the time being since I'm in a crunch for the project that relies on this piece.


The Wish List
-----------

Create a panel that administrators can access, from which they can:

  a. List users (DONE)
  b. Invite users via email with customized message (DONE)
  c. Edit user details post-reg
  d. Edit user ACL data


Create a user side where they can
  a. Access a verify-your-email panel using an admin-sent invite (DONE)
  b. Complete their registration details (against ZFCUser Entity) (DONE)
  c. Limit visible fields based on module config, may not want all Entity data to be populated by user (DONE)
  d. Receive a thanks for registering email post-reg (DONE)

Edit this markdown file to include config examples (check module config for now)



Version
-

0.1

Requires
-----------

* ZFCUser
* BjyAuthorize
* Doctrine2
* JQuery

Installation
--------------

Add this line to your composer.json:
"saeven/circlical-acl-admin" : "dev-master"

Then, run the SQL create found in the data folder


Usage
---------------

If you haven't set up your BjyAuthorize and ZFCUser yet, do that first

- Log into your application as an administrative user (role admin).  If you need to change the role type, just override the guard for CirclicalACLAdmin
- Access /acl-admin
- Should see dashboard


