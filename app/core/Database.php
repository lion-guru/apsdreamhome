<?php
// Backward compatibility shim
// Trigger loading of the actual Database class, which defines the alias.
class_exists('App\\Core\\Database\\Database', true);
