# EQdkp

This is a mirror of EQdkp's codebase. The project has been abandoned, so this
repository is only for historical purposes.

At the height of its popularity, it was powering more than 500,000 sites.

## Project History

EQdkp was started in December 2001, and was one of my very early PHP projects.
It marked the first time I used version control, and was the first piece of
code I ever released as open source.

Version 1.2, the last version I personally released, was released in October of
2003. By that point I was annoyed with the code and made several misguided
attempts to rewrite the project from scratch.

In 2005 I was losing interest in the project altogether, and another developer
actually started contributing back to the project. Since this was the first
time that had happened, I welcomed any and all contributions. Unfortunately,
not all of them were good. A database backup interface was added that was the
source of more than one security vulnerability, and a SOAP interface was added,
which I would be shocked if anyone ever used. This was released as version
1.3.0 in 2005 and then 1.3.2 in 2007, which marked the height of the project's
popularity.

By early 2008 the previous developer working on updates had long since
disappeared and more security issues with the 1.3 version were being
discovered. Disgusted with the state of the project myself and a new developer
set out to fix the major security issues, mostly related to XSS and SQL
injection. But with a renewed interest in the now seven-year-old project, we
ended up refactoring almost every piece of the project.

Both myself and the other developer fell in love with Ruby and with Rails in
late 2008, and by early 2009 had completely lost interest in our refactor,
almost certainly never to pick it back up again.

### The short version

With a few exceptions, the code you see here is among the first stuff I'd ever
written, so by no means should it be an indicator of anything other than how
terrible PHP code can be *when written by amateurs*. If you're new to PHP, use
this as an example of what not to do.

## Source History

The project started on SourceForge's CVS service. When I realized CVS was
terrible and SVN was pretty awesome (at the time), I moved the source control
to a privately-hosted SVN server, since SourceForge didn't change with the
times and Google Code wasn't yet available. When Google Code launched, the
source control was moved there, but by this point we had already lost the CVS
history and then the early SVN history.
