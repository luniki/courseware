define(['assets/js/author_view', 'assets/js/url'], function (AuthorView, helper) {
    'use strict';

    return AuthorView.extend({

        events: {
            "click button[name=save]":   "onSave",
            "click button[name=cancel]": "switchBack",
            "change select[name=test_id]": "setMaxNum"
        },

        initialize: function(options) {
        },

        render: function() {
            return this;
        },
        
        postRender: function() {
            var $input = this.$("input[name='test_questions']");
            var $selection = this.$('select[name="test_id"] option:selected');
            if(typeof $selection.attr('select-data') != "undefined") {
                var options = $.parseJSON($selection.attr('select-data'));
                var $count = parseInt(options.count);
                $input.attr('max', $count);
            }
        }, 
        
        setMaxNum: function() {
            var $input = this.$("input[name='test_questions']");
            var $selection = this.$('select[name="test_id"] option:selected');
            
            var options = $.parseJSON($selection.attr('select-data'));
            var $count = parseInt(options.count);
            $input.val($count);
            $input.attr('max', $count);
        
            
        },

        onSave: function () {
            var view = this;
            var $test_id = this.$('select[name="test_id"]').val();
            var $test_questions = this.$('input[name="test_questions"]').val();
            var $test_questions_min = this.$('input[name="test_questions"]').attr('min');
            var $test_questions_max = this.$('input[name="test_questions"]').attr('max');
            
            if ((!$test_questions)|| ($test_questions > $test_questions_max) || ($test_questions < $test_questions_min)){ $test_questions = 1;}
            
            helper
                .callHandler(this.model.id, 'modify_test', {test_id: $test_id, test_questions: $test_questions} )
                .then(
                    function () {
                        view.switchBack();
                    },
                    function (error) {
                        var errorMessage = 'Could not update the block: '+jQuery.parseJSON(error.responseText).reason;
                        alert(errorMessage);
                        console.log(errorMessage, arguments);
                    }
                ).done();
        }
    });
});
