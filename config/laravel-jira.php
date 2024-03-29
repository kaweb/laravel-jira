<?php

return [
    /**
     * This is the account that has been set up to access Jira via the API
     * this account should have sufficient privileges to create and edit
     * new tickets in Jira
     */
    'jira_user' => getenv('JIRA_USER'),

    /**
     * The Jira API access key for the user defined in jira_user
     */
    'jira_key' => getenv('JIRA_KEY'),

    /**
     * The project prefix that tickets should be applied to in this project
     * instance
     */
    'jira_project' => '',

    /**
     * The url of the Jira instance, this should be the full path before the
     * api route, this is likely to be the same route across all projects
     */
    'jira_path' => '',

    /**
     * Configuration options specific to exception handling with Jira i.e.
     * creating / editing a ticket in Jira whenever an exception has occurred
     */
    'exception_handling' => [
        /**
         * The exception handler used by the application, once tickets have been
         * created / edited, the error will be passed onto this error handler to
         * process the error in the way that Laravel normally does.
         * Leave this empty and the package will try to use the handler found in
         * Laravel's default installation path i.e. app/Exceptions/Handler.php
         */
        'default_handler' => '',

        /**
         * Set any app environments which if the application is set to shouldn't
         * automatically create or edit a Jira ticket. This will compare the app
         * environment setting in the app's .env file.
         * Note that this setting is case insensitive
         */
        'ignore_envs' => [
            'local',
        ],

        /**
         * The default priority of any tickets that are raised by the Jira error
         * tracking application. This value should be 'as seen' in your Jira app
         * when creating a new ticket.
         */
        'default_priority' => 'Critical',

        /**
         * The default type of any tickets that are raised by the Jira error
         * tracking application. This value should be 'as seen' in your Jira app
         * when creating a new ticket.
         */
        'default_type' => 'Alert',

        /**
         * The default person that any tickets raised by the Jira error tracking
         * application will be assigned to. This value should be an exact match
         * to the assignee select value in HTML source on your Jira app. You can
         * leave this setting blank for tickets to be unassigned.
         */
        'default_assignee' => '',
    ],
];