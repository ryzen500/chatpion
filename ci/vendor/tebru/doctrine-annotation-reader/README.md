Doctrine Annotation Reader
==========================

This library wraps a Doctrine annotation reader in order to provide
a cleaner interface to parsing class and interface annotations.

AbstractAnnotation
------------------

This is the base class that must be extended from to use this library.

Override `init` if the annotation data schema is different from a single
value key. Override `allowMultiple` and return true if multiple instances
of this annotation is allowed to be stored. Override `getName` if you
want to use a different name than the class name.

AnnotationReaderAdapter
-----------------------

This class accepts a Doctrine `Reader` interface and Doctrine
`CacheProvider`. It has three public methods of interest. Each one
returns an `AnnotationCollection`.

The `readClass` method takes a class name and `$useParent` as
parameters. If `$useParent` is true, the reader will look at the
super class or all interfaces is the provided class is an interface.

The `readMethod` and `readProperty` operate the same. They both accept
the name of the method or property, the class name, `$useParent`, and
`$useClass` as parameters. The `$useParent` parameter works the same
as `readClass`. The `$useClass` parameter–if true–inherits annotations
from the class as well. If `$useParent` is true, the parent class
annotations will also be added.

If multiple annotations are not allowed, any time a duplicate is found,
it will be ignored. This allows methods to override class level
annotations, for example.

AnnotationCollection
--------------------

This is returned from the reader. You can get single annotations using
`get` and annotations that allow multiple using `getAll`, which returns
an array. Both methods take the annotation name as a parameter, which
is defined by the annotation and defaults to the class name.
