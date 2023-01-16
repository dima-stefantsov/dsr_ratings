**rating_calculators for https://ds-rating.com/ratings**

code is very simple:
1. DSR will call the `main($teams)` function for every game in the database, in order games was played
2. calculator responsibility is to change `$player['rating']` field for every player in the game

you can read rating_calculators code in [/calculators/](/calculators/) folder, start with [pink_color_rating.php](/calculators/pink_color_rating.php)

you can change calculators code, or even write you own rating_calculator!

--

`/sandbox/` will let you test available calculators on mock games data, it will also help you write your own rating_calculator

open windows command prompt, and then use `/sandbox/run.bat` to run a calculator over sample games

use command `run pink_color_rating` to set the rating_calculator you want to use

also feel free to change any code in `/sandbox/`, especially [/sandbox/src/_run.php](/sandbox/src/_run.php), it was made for you to change it!

there are no external dependencies, code requires PHP, and it is already bundled in the repository

please enjoy writing your own rating_calculators!

--

give feedback in [discord](https://discord.com/invite/KXKw8HqKKK)
