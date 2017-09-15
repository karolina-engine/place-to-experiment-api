<template>

</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'tags',
    data() {
        return {
            selectedTag: '',
            selectedTags: [],
            selectedTagsIds: [],
            searchTerm: '',
            showEditor: false
        }
    },
    props: {
        allTags: {
            required: true,
            type: Array
        },
        displayTags: {
            required: true,
            type: Array
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
    methods: {
        setup: function() {
            this.selectedTags = []
            if (this.displayTags) {
                this.selectedTags = JSON.parse(JSON.stringify(this.displayTags))
                this.selectedTags = this.sortTagsByLabel(this.selectedTags)
            }
        },
        initializeEditor: function() {
            if (this.showEditor === false) {
                this.showEditor = true
                this.$nextTick(() => {
                    if (this.$refs.searchInputRef) {
                        this.$refs.searchInputRef.focus()
                    }
                })
            }
        },
        addTag: function(tag) {
            // debug info
            this.debug('Verifying tag: ' + tag.id)
            var tagAlreadyAdded = false
            for (var i = 0; i < this.selectedTags.length; i++) {
                if (tag.id === this.selectedTags[i].id) {
                    tagAlreadyAdded = true
                    // debug info
                    this.debug('tag already added')
                }
            }
            if (!tagAlreadyAdded) {
                this.selectedTags.push(tag)
                this.selectedTags = this.sortTagsByLabel(this.selectedTags)
                // debug info
                this.debug('tag valid')
            }
        },
        removeTag: function(tagId) {
            for (var i = 0; i < this.selectedTags.length; i++) {
                if (this.selectedTags[i].id === tagId) {
                    var index = this.selectedTags.indexOf(this.selectedTags[i])
                    this.selectedTags.splice(index, 1)
                    this.debug('Removing tag: ' + tagId)
                }
            }
        },
        saveTags: function() {
            if (this.isModified) {
                for (var i = 0; i < this.selectedTags.length; i++) {
                    this.selectedTagsIds.push(this.selectedTags[i].id)
                }
                // emit event so that parent can react
                this.$emit('add-tags', this.selectedTags, this.selectedTagsIds)
                if (this.showEditor === true) {
                    this.showEditor = false
                }
            } else {
                this.debug('Tags not modified')
            }
        },
        resetTags: function() {
            // debug info
            this.debug('Resetting tags')
            if (this.showEditor === true) {
                this.showEditor = false
            }
            this.setup()
        },
        sortTagsByLabel: function(tagsArray) {
            return tagsArray.sort(function(a, b) {
                var textA = a.label.toLowerCase()
                var textB = b.label.toLowerCase()
                return (textA < textB) ? -1 : (textA > textB) ? 1 : 0
            })
        },
        tagLabelFilter: function(tags, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                this.debug('serching tags by keyword: ' + searchTerm)
                return tags.filter(function(tags) {
                    var tagText = tags.label.toLowerCase()
                    if (tagText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return tags
            }
        },
        searchTags: function() {
            this.tagLabelFilter(this.allTags, this.searchTerm)
        }
    },
    computed: {
        isModified: function() {
            if (this.displayTags) {
                var sortedTags = JSON.parse(JSON.stringify(this.displayTags))
                sortedTags = this.sortTagsByLabel(sortedTags)
                return JSON.stringify(this.selectedTags) !== JSON.stringify(sortedTags)
            } else {
                return this.selectedTags.length !== 0
            }
        }
    },
    watch: {
        displayTags: function(val) {
            this.setup()
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
        // set this up
        this.setup()
    }
}
</script>
