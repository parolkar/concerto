/**
 * @fileOverview The "codemirror" plugin. It's indented to enhance the
 *  "sourcearea" editing mode, which displays the xhtml source code with
 *  syntax highlight and line numbers.
 * @see http://marijn.haverbeke.nl/codemirror/ for CodeMirror editor which this
 *  plugin is using.
 */

CKEDITOR.plugins.add( 'codemirror', {
    requires : [ 'sourcearea' ],
    /**
         * This's a command-less plugin, auto loaded as soon as switch to 'source' mode  
         * and 'textarea' plugin is activeated.
         * @param {Object} editor
         */

    init : function( editor ) {
        editor.on( 'mode', function() {
            if ( editor.mode == 'source' ) {
                var sourceAreaElement = editor.textarea,
                holderElement = sourceAreaElement.getParent();
                var holderHeight = holderElement.$.clientHeight + 'px';
                
                var codemirrorInit = 
                CodeMirror.fromTextArea(editor.textarea.$,{
                    mode:"htmlmixed",
                    theme:"neat",
                    fixedGutter:false,
                    lineNumbers:true,
                    matchBrackets:true,
                    lineWrapping:true,
                    autoClearEmptyLines:true,
                    indentWithTabs:true
                });
                codemirrorInit.on("change",function(instance){
                    instance.save();
                    instance.refresh();
                });
                codemirrorInit.setSize(775,null);
                codemirrorInit.refresh();
                
                editor.on( 'beforeCommandExec', function( e ){
                    e.removeListener();
                    editor.textarea.setValue( codemirrorInit.getValue() );
                    editor.fire( 'dataReady' );
                } );
                                                   
                CKEDITOR.plugins.mirrorSnapshotCmd = {
                    exec : function( editor ) {
                        if ( editor.mode == 'source' ) {
                            editor.textarea.setValue( codemirrorInit.getValue() );
                            editor.fire( 'dataReady' );
                        }
                    }
                };
                
                CKEDITOR.plugins.mirrorRefreshCmd = {
                    exec : function( editor ) {
                        if ( editor.mode == 'source' ) {
                            codemirrorInit.refresh();
                        }
                    }
                };
                
                editor.addCommand( 'mirrorSnapshot', CKEDITOR.plugins.mirrorSnapshotCmd );
                editor.addCommand( 'mirrorRefresh', CKEDITOR.plugins.mirrorRefreshCmd );
            }
        } );
    }

});