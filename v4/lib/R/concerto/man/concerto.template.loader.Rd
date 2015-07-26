\name{concerto.template.loader}
\alias{concerto.template.loader}
\title{
Loader template.
}
\description{
Sets a template that will be shown when the proper templates are loaded.
}
\usage{
concerto.template.loader(templateID=-1, HTML="", head="", params = list(), workspaceID = concerto$workspaceID, effectShow="default", effectShowOptions="default", effectHide="default", effectHideOptions="default")
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
response1 <- concerto.template.loader(1)
response2 <- concerto.template.loader(HTML="<div align="center">loading ... please wait ...</div>")
}

\keyword{ template }
\keyword{ HTML }
