\name{concerto.template.show}
\alias{concerto.template.show}
\title{
Show template to test participant.
}
\description{
Display template to test participant.
}
\usage{
concerto.template.show(templateID=-1, HTML="", head="", params = list(), timeLimit = 0, finalize = F, workspaceID = concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default", effectHideOptions="default")
}

\arguments{
  \item{templateID}{
Template ID or name.
}

 \item{HTML}{
Template HTML.
}

\item{head}{
Template head tag.
}

  \item{params}{
Named list with parameters we want to be inserted into template HTML.
}
  \item{timeLimit}{
Time limit after which template will be auto submitted (in seconds).
}
  \item{finalize}{
Logical: TRUE if after this template there is no more logic available.
}
  \item{workspaceID}{
Concerto workspace ID where template object is located.
}

\item{effectShow}{
Template show transition effect.
}

\item{effectHide}{
Template hide transition effect.
}

\item{effectShowOptions}{
Template show transition effect options list.
}

\item{effectHideOptions}{
Template hide transition effect options list.
}

}
\details{
}
\value{
Returns named list with template specific input gathered from user and static variables:
\item{LAST_PRESSED_BUTTON_NAME}{character: name of the button that submitted the template }
\item{OUT_OF_TIME }{numeric: 1 if time limit has been reached and 0 if it hasn't}
\item{TIME_TAKEN }{numeric: time taken to submit the template}
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
response1 <- concerto.template.show(1)
response2 <- concerto.template.show(HTML="<div align="center">Hello world!</div>")
}

\keyword{ template }
\keyword{ HTML }
