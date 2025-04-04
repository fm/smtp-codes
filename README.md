# Welcome to smtp.codes

We created this guide to help you understand what the SMTP codes that various email servers respond with. This is super useful when troubleshooting email delivery and a tool every email marketer, deliverability person or email engineer should have in their tool kit.

## Contribute

Ran into an SMTP code that wasn't very helpful and you figured out what it means? or maybe you work for an email provicer and want to contribute the codes your services returns so you can decrease your support volume? We'd love to help you document them! 

If you plan on making additions or edits to existing SMTP responses, you’ll need to fork the repo and [submit a pull request](https://help.github.com/en/articles/creating-a-pull-request). All of the data is stored as JSON in the [/data](https://github.com/fm/smtp-codes/tree/main/data) folder. There's no need to set up the development environment for changes like this.

Feel free to [file an issue](https://github.com/fm/smtp-codes/issues/new) if you don’t feel like dealing with code. If you do not have a GitHub account, you can also submit them via email at [submit@smtp.codes](mailto:submit@smtp.codes?subject=New%20code%20submission&body=Full%20SMTP%20response%3A%0AEmail%20provider%2Fserver%20returning%20the%20code%3A%0AAny%20other%20details%3A)

## Deployment
Currently, when a push event happens, the site is deployed to our server via a webhook script which is currently not part of the repo. In the future, we will move to using GitHub Actions & Pages.

## Issues & Comments
Feel free to contact us if you encounter any issues. Please leave all comments, bugs, requests and issues on the Issues page.

## License
smtp.codes is licensed under the MIT license. Please refer to the [LICENSE](https://github.com/fm/smtp-codes/blob/main/LICENSE) for more information.

## Acknowledgements
We'd like to thank the ActiveCampaign Postmark team for spear-heading the initiative with SMTP Field Manual, from where we based the initial scope for this project.