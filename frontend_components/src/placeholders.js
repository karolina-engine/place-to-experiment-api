export default {

    experiment: {
        stage: '0',
        experiment_id: '1',
        custom_language: false,
        language: false,
        tags: [],
        team: null,
        links: []
    },

    experiment_preview: {
        stage: '0',
        experiment_id: '1',
        image: null,
        owner_name: '',
        title: 'Experiment title',
        short_description: 'Short description.'
    },

    acl: ['view'],

    profile: {
        user_id: 1,
        first_name: '',
        last_name: '',
        short_description: '',
        long_description: '',
        tags: [],
        links: []
    },

    profile_preview: {
        user_id: 1,
        first_name: '',
        last_name: '',
        short_description: '',
        image: null,
        links: [],
        tags: []
    },

    supportedEditors: [
        'sirtrevor',
        'quill'
    ],

    defaultPlaceholderImageUrl: '/img/placeholder.png',
    defaultPlaceholderVideoUrl: '/img/placeholder_video.png',
    defaultPlaceholderProfileUrl: '/img/placeholder_profile.png'
}
