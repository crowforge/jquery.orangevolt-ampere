<?php
	$LESS[] = '/examples/' . basename( __DIR__) . '/calculator.less';
?>

<script id="calculator-intro" type="text/my-template">
<div>
	<center>This application represents a simple calculator implementation using <a href="#ampere">Ampere</a>.</center>
	<br>
	<center>It's kind of useless but shows the power of <a href="#ampere">Ampere</a>.</center> 
</div>
<p>
	<center>
		<button 
			class="btn-primary btn-large " 
			ng-ampere-transition="$ampere.view.state().transitions.main" 
			xtitle="Click to start">
			Start
		</button>
	</center>
</p>
</script>

<script>
	var calculator = window.ov.ampere().module( 'calculator', function meeep( module) {
		this.state( function main( state) {
			state.view( 'intro', $('#calculator-intro'))
			.options({
				'ampere.ui.caption' : null,
				'ampere.ui.description' : null
			});

			state.view
				.load( 'main', '/examples/<?php echo basename( __DIR__)?>/calculator-main.fragment')
				.options( {
					'ampere.ui.caption' : null,
					'ampere.ui.description' : null
				});

			state.onPlusMinus = function() {
				return (-parseFloat( state.value)).toString();
			};

			state.isPlusMinusDisabled = function() {
				return !$.isNumeric( state.value) || state.value==0;
			};

			//window.deferred = $.Deferred();
			//return window.deferred;
		})
		.options( {
			'ampere.ui.description' : 'description of main state',
			'ampere.state.view' 	: 'main'
		})
		.view.load( 'help', '/examples/<?php echo basename( __DIR__)?>/calculator-help.fragment')
			.options({
				'ampere.ui.caption' : 'Help',
				'ampere.ui.description' : null
			})
		.state()
			.view( 'about', 'This is the <strong>about</strong> view content of application {{ampere.ui.getCaption( ampere.module)}}')
			.options({
				'ampere.ui.caption' : 'About',
				'sampere.ui.description' : null
			})
		;

		this.states.main.value = '';
		this.states.main.result = 0; 
		
		function valueIsNumeric() {
			return $.isNumeric( $.trim( module.states.main.value));
		}
		
		this.states.main
			.transition( this.states.main)
			.options({
				/* okay : no effect because the template button element provides a non empty text*/'ampere.ui.caption' : 'Alarm',
				'ampere.ui.icon'	: 'icon-home',
				'ampere.ui.description' : 'Start application'
			})
		.state()
			.transition( 'mul', this.states.main)
			.action( function action( transition) {
				return function redo( source, target) {
					transition.state().result = transition.state().result * parseFloat( transition.state().value);
					transition.state().value = '';    
				};
			}) 
			.enabled( valueIsNumeric)
		.state()
			.transition( 'div', this.states.main)
			.action( function action( transition) {
				return function redo( source, target) { 
					transition.state().result = transition.state().result / parseFloat( transition.state().value);
					transition.state().value = '';    
				};
			})
			.enabled( function() {
				var value = $.trim( module.states.main.value);
				return $.isNumeric( value) && value!=0; 
			})
			.options( 'ampere.ui.description', function() {
				return parseFloat( this.state().value)==0 ? 'Division by zero is forbidden' : undefined;
			})
		.state()
			.transition( 'add', this.states.main)
			.action( function action( transition) {
				var result = transition.state().result;
				var value = transition.state().value;
				
				return function redo( source, target) {
					target.result = source.result + parseFloat( source.value);
					target.value = '';

					return function undo() {
						source.result = result;
						source.value = value;
					};
				};
			})
			.enabled( valueIsNumeric)
		.state()
			.transition( 'sub', this.states.main)
			.action( function action( transition) {
				return function redo( source, target) {
					transition.state().result = transition.state().result - parseFloat( transition.state().value);
					transition.state().value = '';
				};    
			})
			.enabled( valueIsNumeric)
		.state()
			.transition( 'ac', this.states.main)
			.action( function action( transition) {
				return function redo( source, target) {
					transition.state().result = 0;
					transition.state().value = '';
				};
			})
			.enabled( function() {
				return module.states.main.result && module.current().view===module.states.main.views.main;
			}) 
			.options( { foo : 'bar', 'ampere.ui.type' : 'primary'})
		;

		function isNotCurrentView( view) {
			return function() {
				return module.current().view!==view;
			};
		}
		
		this
			.transition( 'intro', this.states.main)
			.enabled( isNotCurrentView( module.states.main.views.intro))
			.options({
				'ampere.ui.description' : 'bla bla',
				'ampere.ui.icon'		: 'icon-info-sign',
				'ampere.state.view'		: 'intro'
			});
		this
			.transition( 'help', this.states.main)
			.enabled( isNotCurrentView( module.states.main.views.help))
			.options({
				'ampere.ui.icon'		: 'icon-question-sign',
				'ampere.state.view'		: 'help'
			});
		this.transition( 'about', this.states.main)
			.enabled( isNotCurrentView( module.states.main.views.about))
			.options({
				'ampere.state.view'		: 'about'
			});

		
		this.state( function dummystate( state) {
			state.transition( 'about', module.states.main)
			.options({ 
				'ampere.ui.type'    : 'secondary',
				'ampere.state.view'	: 'about'
			});
		})
		.transition( 'intro', this.states.main).options({ 'ampere.state.view'	: 'intro'})
		.state().transition( 'help', this.states.main).options({ 'ampere.state.view'	: 'help'})
		.state().transition( this.states.main);
		
		this.states.main.transition( this.states.dummystate)
		.options( 'ampere.ui.type' , 'secondary');

		this.state( function deferredstate( state) {
			state.transition( module.states.main)
			.action( function action( transition) {
				return function redo( source, target) {
					window.action = $.Deferred();
					window.action.notify( 'Enter "window.action.resolve(), window.action.reject( [message]) or window.action.notify( [message], [progressInPercent]) in console"');
					return window.action;
				};
			})
			.options({ 
				'ampere.state.view'	: 'intro'
			});
		});
		this.transition( this.states.deferredstate)
		.action( function action( transition) {
			return function redo( source, target) {
					// simulate progress of something
				return $.Deferred( function() {
					var deferred = this;
					
					function timeout( counter) {
						deferred.notify( 'Step ' + counter + ' ... ', counter*10 + '%');
						window.setTimeout( function() {
							if( counter<10) {
								timeout( counter+1);
							} else {
								deferred.resolve( 'Computation done.');
							} 
						}, 500);
					}

					timeout( 1);
				});
			};
		})
		.options( 'ampere.ui.type' , 'secondary');

		this.transition( 'exception_spiced_transition_action', this.states.main)
		.action( function action( transition) {
			var firstTime = true
			
			return function redo( source, target) {
				if( firstTime) {
					firstTime = false;
					throw 'a problem occured';
				} else {
					return function undo() {
						return redo;
					}
				} 
			};
		})
		.options( 'ampere.ui.type' , 'global');

		
		 
		//this.deferred = $.Deferred();
		//return this.deferred;
	}).defaults( {
		'ampere.baseurl' 		: '/lib/ampere',
		'ampere.ui.description' : 'This is a simple <em>calculator</em> app.',
		'ampere.ui.about' 		: $('<div>This is a sample <a href="#ampere">Ampere</a> application.</div>'),
		'ampere.ui.about.url'	: 'http://www.orangevolt.com',
		'ampere.state'	  		: 'main',
		'ampere.view'	  		: 'intro',
		'aampere.ui.caption'	: null,
		'aampere.ui.description': null/*,
		'ampere.history.limit'	: Number.MAX_VALUE */
	});
	
	$( 'body').ampere( new calculator());
</script>
