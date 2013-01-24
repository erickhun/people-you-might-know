<?php

  /*
   * How important are theses combinations?
   */
  define('WEIGHT_SCHOOL',                 1); # Same school
  define('WEIGHT_SCHOOL_WORK',            5); # Same school and work
  define('WEIGHT_SCHOOL_LOCATION',        4); # Same school and location
  define('WEIGHT_SCHOOL_LOCATION_WORK',   10);# Same school, location, and work
  define('WEIGHT_SCHOOL_BIRTH',           3); # Same school and ~range of age

  define('WEIGHT_LOCATION',               1); # Same location
  define('WEIGHT_LOCATION_BIRTH',         2); # Same location and ~range of birth
  define('WEIGHT_LOCATION_WORK',          10);# Same location and work
  define('WEIGHT_LOCATION_SEEN',          2); # Same location and spy profile

  define('WEIGHT_WORK',                   1); # Same work

  define('WEIGHT_TAG',                    15);# Tagged together on picture
  define('WEIGHT_SEEN',                   1); # Spy your profile

  define('WEIGHT_COMMON',                 2); # Common friends
  define('WEIGHT_COMMON_SCHOOL',          10); # Common friends, same school
  define('WEIGHT_COMMON_WORK',            10); # Common friends, same work
  define('WEIGHT_COMMON_LOCATION',        7); # Common friends, same location

  # and more and more...

  define('MAX_AGE',                       4); #Range of date people know each other
  
?>