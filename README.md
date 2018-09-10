# Point Tracker

This plugin is essentially a form builder, but for challenges and contests.  After installation, a site admin needs to create a challenge inserting a name, start and end dates, a description, and decide if they want to require users to be approved (which requires site account).

There is a global option under `Settings` -> `PT Settings` for requiring the participants be logged into an account before they are allowed to access any of the challenge pages.

Once saved, the admin then navigates to the `Point Tracker` -> `Activities` admin menu and after selecting the challenge they just created, they add activities they want the participants to complete.  The following fields are allowed:

- Type (checkbox, text, radio, number, long text)
- Name (short name not displayed)
- Point value (the amount of points to be awarded each time this activity is done)
- Max allowed (the maximum amount of points allowed to be collected during this challenge)
- Question (the short question to ask the user)
- Description (a longer description of the question and requirements for it's completion)
- Order (the order in which the question will appear to the user)
- Label (only available in checkbox and radio button questions, but a comma delimited list of options that will be presented to the user)
- Min/Max (only available in numeric and text questions, but the minimum and maximum amount allowed)
- Group (allows admin to organize activities into groups with a header)
- Hidden (hides the activity from challenge totals)

After creating all activities, you can go back to the main Point Tracker admin page and copy the link and send or post it to those that want to participate.  Once in the challenge (depending on your previous selections), participants will be able to independently save activities they've accomplished.  So for example, one of your numeric activities is "Make a phone call" and you award 5 points for doing it.  When the participant goes to the page to complete their entry, they might say they made 5 phone calls and put 5 in the entry box and click "Save" for that activity.  The system will do the math and calculate they earned 25 points for completing that task.  This will then show on the admin Dashboard under `Point Tracker` -> `Participant`.  Which reflects a list of all participants and their point tallys.

The `Point Tracker` -> `Log` page shows a raw log of all entries that have been made and the ability for the admin to delete an entry and allow the participant to reenter or forfeit those points.  They will automaticaly be removed from any total point calculations.

Once the challenge is complete the admin can go to the `Point Tracker` -> `Participants` page to see the person with the most points.  As part of your challenge process, I recommend that you validate the entries by the participants to make sure things were entered and recorded correctly.