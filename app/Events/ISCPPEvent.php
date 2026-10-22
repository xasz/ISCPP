<?php

namespace App\Events;

/**
 * Marker interface for every ISCPP event.
 *
 * This is what AppServiceProvider listens on. It MUST be an interface: Laravel's
 * event dispatcher resolves listeners through class_implements() only
 * (Dispatcher::addInterfaceListeners), so a listener bound to a parent *class*
 * is never invoked for its subclasses.
 */
interface ISCPPEvent {}
