<template>


</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
import SirTrevor from 'sir-trevor'
import {
    quillEditor
} from 'vue-quill-editor'
export default {
    name: 'profile-language',
    data() {
        return {
            languageText: '',
            editorInstance: null,
            showEditor: false,
            showErrors: false,
            supportedEditors: this.common.placeholders.supportedEditors,
            quillEditorConfig: {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
                        ['blockquote'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        ['clean'], // remove formatting button
                        ['link', 'image', 'video']
                    ]
                }
            }
        }
    },
    props: {
        value: { // cannot check for String type because profilePage@getLanguage returns null for non-existing language
            required: true
        },
        languageKey: {
            required: true,
            type: String
        },
        format: {
            required: true,
            type: String
        },
        editor: { // Optional. Can be either 'SirTrevor' or 'Quill'
            required: false,
            default: false
        },
        editorInitialValue: { // Optional. Required for Sir Trevor as the initial JSON
            required: false,
            default: false
        },
        editorConfig: { // Optional. An object of configuration options for the editor
            required: false,
            default: false
        },
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        }
    },
    mixins: [
        auth,
        helpers
    ],
    components: {
        quillEditor
    },
    methods: {
        setup: function() {
            this.languageText = this.value
            if (this.editor) {
                if (!(this.supportedEditors.indexOf(this.editor.toLowerCase()) > -1)) {
                    // debug info
                    this.debug('provided editor is not supported')
                } else {
                    if (this.editor.toLowerCase() === 'sirtrevor') {
                        if (this.format.toLowerCase() !== 'json') {
                            // debug info
                            this.debug('format for SirTrevor should be JSON')
                        } else {
                            // debug info
                            this.debug('SirTrevor editor is ready')
                        }
                    } else if (this.editor.toLowerCase() === 'quill') {
                        if (this.format.toLowerCase() !== 'html') {
                            // debug info
                            this.debug('format for Quill should be HTML')
                        } else {
                            // merge config
                            this.quillEditorConfig = Object.assign({}, this.quillEditorConfig, this.editorConfig)
                            // debug info
                            this.debug('quill editor is ready')
                        }
                    }
                }
            }
        },
        initializeEditor: function() {
            if (this.showEditor === false) {
                this.showEditor = true
                this.$nextTick(() => {
                    if (this.$refs.customTextInputRef) {
                        this.$refs.customTextInputRef.focus()
                    } else if (this.$refs.quillEditorRef) {
                        // TODO: this doesn't work because ql-editor has no tabindex attribute. Setting it to 'tabindex=0' should fix this
                        this.$refs.quillEditorRef.$el.children[0].focus()
                    }
                })
                if (this.editor) {
                    if (this.editor.toLowerCase() === 'sirtrevor' && this.format.toLowerCase() === 'json') {
                        if (!this.editorInstance) {
                            this.editorInstance = this.initializeSirTrevor()
                        } else {
                            this.editorInstance = this.reinitializeEditor()
                        }
                    }
                }
            }
        },
        collapseEditor: function() {
            if (this.showEditor === true) {
                this.showEditor = false
                if (this.isModified) {
                    // debug info
                    this.debug('resetting editor content')
                    if (!this.editor) {
                        if (this.value) {
                            this.languageText = this.value
                        } else {
                            this.languageText = ''
                        }
                    }
                }
                // reset validator errors
                this.errors.clear()
                this.showErrors = false
            }
        },
        reinitializeEditor: function() {
            if (this.editor.toLowerCase() === 'sirtrevor' && this.format.toLowerCase() === 'json') {
                this.editorInstance.destroy()
                return this.initializeSirTrevor()
            }
        },
        initializeSirTrevor: function() {
            if (this.editorConfig.id) {
                if (this.editorInitialValue !== false) {
                    if (this.editorConfig.fileUploadUrl && this.editorConfig.iconUrl) {
                        var authHeader = this.getAuthorizationHeaderMixin()
                        // TODO: remove jquery dependency by adding a reactive value to the textarea in HTML
                        window.$('#' + this.editorConfig.id).val(this.editorInitialValue)
                        SirTrevor.setDefaults({
                            iconUrl: this.editorConfig.iconUrl,
                            uploadUrl: this.editorConfig.fileUploadUrl,
                            attachmentFile: 'file',
                            formatBar: {
                                commands: [{
                                    name: 'Bold',
                                    title: 'bold',
                                    iconName: 'fmt-bold',
                                    cmd: 'bold',
                                    keyCode: 66,
                                    text: 'B'
                                }, {
                                    name: 'Italic',
                                    title: 'italic',
                                    iconName: 'fmt-italic',
                                    cmd: 'italic',
                                    keyCode: 73,
                                    text: 'i'
                                }, {
                                    name: 'Link',
                                    title: 'link',
                                    iconName: 'fmt-link',
                                    cmd: 'linkPrompt',
                                    text: 'link'
                                }, {
                                    name: 'Unlink',
                                    title: 'unlink',
                                    iconName: 'fmt-unlink',
                                    cmd: 'unlink',
                                    text: 'link'
                                }, {
                                    name: 'Heading',
                                    title: 'heading',
                                    iconName: 'fmt-heading',
                                    cmd: 'heading',
                                    text: 'heading'
                                }]
                            },
                            ajaxOptions: {
                                headers: {
                                    'Authorization': authHeader
                                }
                            }
                        })
                        var sirTrevorConfig = {
                            el: document.querySelector('#' + this.editorConfig.id),
                            blockTypes: ['Heading', 'Text', 'Image', 'Video'],
                            defaultType: 'Text'
                        }
                        var editor = new SirTrevor.Editor(sirTrevorConfig)
                        // debug info
                        this.debug('initializing SirTrevor')
                        return editor
                    } else {
                        // debug info
                        this.debug('SirTrevor requires file upload url and icon url')
                    }
                } else {
                    // debug info
                    this.debug('SirTrevor editor requires initial value')
                }
            } else {
                // debug info
                this.debug('SirTrevor editor requires the element id')
            }
        },
        updateText: function() {
            var languageBody = {}
            if (!this.editor) {
                // debug info
                this.debug('checking regular text')
                if (this.languageText !== this.value) {
                    this.$validator.validateAll().then(success => {
                        if (success) {
                            // debug info
                            this.debug('language text form is valid')
                            languageBody = {
                                [this.languageKey]: this.languageText
                            }
                            this.$emit('profile-language-updated', languageBody)
                            // collapse editing part
                            this.showEditor = false
                        } else {
                            this.showErrors = true
                            // debug info
                            this.debug('language text form has errors')
                        }
                    })
                } else {
                    // debug info
                    this.debug('language text not modified')
                }
            } else {
                if (this.editor.toLowerCase() === 'sirtrevor' && this.format.toLowerCase() === 'json') {
                    // debug info
                    this.debug('checking SirTrevor text')
                    SirTrevor.onBeforeSubmit()
                    var emptyJson = '{"data":[]}'
                    var jsonContent = this.editorInstance.store.toString()
                    if (!(jsonContent === emptyJson && this.editorInitialValue === null) && (jsonContent !== this.editorInitialValue)) {
                        this.debug('SirTrevor regular')
                        languageBody = {
                            [this.languageKey]: jsonContent
                        }
                        this.$emit('profile-language-updated', languageBody, false, true)
                        // collapse editing part
                        this.showEditor = false
                    } else {
                        // debug info
                        this.debug('SirTrevor text not modified')
                    }
                } else if (this.editor.toLowerCase() === 'quill' && this.format.toLowerCase() === 'html') {
                    // debug info
                    this.debug('checking Quill text')
                    if (!(this.languageText === '' && this.value === null) && (this.value !== this.languageText)) {
                        // debug info
                        this.debug('Quill text is modified')
                        this.debug('Quill regular')
                        languageBody = {
                            [this.languageKey]: this.languageText
                        }
                        this.$emit('profile-language-updated', languageBody, false)
                        // collapse editing part
                        this.showEditor = false
                    } else {
                        // debug info
                        this.debug('Quill text not modified')
                    }
                }
            }
        },
        onQuillEditorChange: function({
            editor,
            html,
            text
        }) {
            this.languageText = html
        }
    },
    computed: {
        isModified: function() {
            if (this.editor) {
                if (this.editor.toLowerCase() === 'sirtrevor' && this.format.toLowerCase() === 'json') {
                    if (this.editorInstance) {
                        // TODO: Need to hook into onChangeEvent for SirTrevor to actially detect isModified
                        SirTrevor.onBeforeSubmit()
                        var jsonContent = this.editorInstance.store.toString()
                        if (jsonContent !== this.editorInitialValue) {
                            return true
                        } else {
                            return false
                        }
                    }
                } else if (this.editor.toLowerCase() === 'quill' && this.format.toLowerCase() === 'html') {
                    if (this.value) {
                        return this.value !== this.languageText
                    } else {
                        return this.languageText !== ''
                    }
                }
            } else {
                if (this.value) {
                    return this.value !== this.languageText
                } else {
                    return this.languageText !== ''
                }
            }
        },
        quillEditorInstance: function() {
            return this.$refs.quillEditorRef.quillEditor
        }
    },
    watch: {
        value: function(val) {
            this.languageText = val
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this, '(' + this.languageKey + ')')
        // set this up
        this.setup()
    }
}

</script>
